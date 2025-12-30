import esbuild from "esbuild";
import fs from "fs";
import path from "path";

const SRC_DIR = path.resolve("src");
const OUT_BASE = path.resolve("../assets/js");

const args = new Set(process.argv.slice(2));
const isWatch = args.has("--watch");

const modeArg = [...args].find((a) => a.startsWith("--mode="));
const mode = modeArg ? modeArg.split("=")[1] : "dev";
const isProd = mode === "prod";

function listIndexEntries(dir) {
  /** @type {string[]} */
  const entries = [];
  const stack = [dir];

  while (stack.length) {
    const current = stack.pop();
    const items = fs.readdirSync(current, { withFileTypes: true });

    for (const item of items) {
      const full = path.join(current, item.name);

      if (item.isDirectory()) {
        stack.push(full);
        continue;
      }

      if (item.isFile() && item.name === "index.ts") {
        entries.push(full);
      }
    }
  }

  return entries;
}

function ensureDir(dir) {
  fs.mkdirSync(dir, { recursive: true });
}

function removeDirContents(dir) {
  if (!fs.existsSync(dir)) return;
  for (const name of fs.readdirSync(dir)) {
    const target = path.join(dir, name);
    fs.rmSync(target, { recursive: true, force: true });
  }
}

function cleanupOutBase(expectedRelativeDirs) {
  if (!fs.existsSync(OUT_BASE)) return;

  const expected = new Set(expectedRelativeDirs.map((p) => p.replace(/\\/g, "/")));

  function walk(current) {
    for (const dirent of fs.readdirSync(current, { withFileTypes: true })) {
      const full = path.join(current, dirent.name);
      const rel = path.relative(OUT_BASE, full).replace(/\\/g, "/");

      if (dirent.isDirectory()) {
        walk(full);

        // if a directory does not correspond to an entry output dir, remove it
        if (!expected.has(rel)) {
          fs.rmSync(full, { recursive: true, force: true });
        }
        continue;
      }

      // Remove stray files that aren't in an expected folder
      const parentRel = path.relative(OUT_BASE, path.dirname(full)).replace(/\\/g, "/");
      if (!expected.has(parentRel)) {
        fs.rmSync(full, { force: true });
      }
    }
  }

  walk(OUT_BASE);
}

async function buildOne(entryFile) {
  const relativeFolder = path.relative(SRC_DIR, path.dirname(entryFile));
  const outDir = path.join(OUT_BASE, relativeFolder);

  ensureDir(outDir);

  // optional: clear previous outputs of this module
  // removeDirContents(outDir);

  const outfile = path.join(outDir, "index.js");

  const ctx = await esbuild.context({
    entryPoints: [entryFile],
    bundle: true,
    outfile,
    platform: "browser",
    target: ["es2020"],
    sourcemap: false,
    minify: true,
    logLevel: "info"
  });

  if (isWatch) {
    await ctx.watch();
  } else {
    await ctx.rebuild();
    await ctx.dispose();
  }

  console.log(`[${isWatch ? "watch" : "build"}:${mode}] ${entryFile} -> ${outfile}`);
}

async function main() {
  const entries = listIndexEntries(SRC_DIR);

  const expectedRelativeDirs = entries.map((entry) =>
    path.relative(SRC_DIR, path.dirname(entry))
  );

  ensureDir(OUT_BASE);
  cleanupOutBase(expectedRelativeDirs);

  if (entries.length === 0) {
    console.log("No src/**/index.ts found.");
    return;
  }

  await Promise.all(entries.map(buildOne));

  if (isWatch) {
    console.log("Watching all entrypoints...");
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
