<?php
declare(strict_types=1);

/** @var string $baseUrl */
/** @var string $viewName */
?>

<section class="space-y-8">
  <!-- Hero / sanity check -->
  <div class="rounded-3xl border border-lyreco-light-gray/70 bg-lyreco-white/80 shadow-sm p-6 sm:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
      <div class="space-y-2">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-lyreco-blue">
          Layout Test Page
        </h1>
        <p class="text-sm sm:text-base text-lyreco-dark-gray max-w-2xl">
          Objectif : tester l’alignement, les espacements, le header sticky, le footer en bas, et le rendu mobile.
        </p>
      </div>

      <div class="flex flex-wrap gap-2">
        <a href="#section-cards" class="rounded-full px-4 py-2 text-sm font-semibold border border-lyreco-light-gray/70 bg-lyreco-white/80 hover:bg-lyreco-green/10">
          Cards
        </a>
        <a href="#section-table" class="rounded-full px-4 py-2 text-sm font-semibold border border-lyreco-light-gray/70 bg-lyreco-white/80 hover:bg-lyreco-green/10">
          Table
        </a>
        <a href="#section-form" class="rounded-full px-4 py-2 text-sm font-semibold border border-lyreco-light-gray/70 bg-lyreco-white/80 hover:bg-lyreco-green/10">
          Form
        </a>
        <a href="#section-long" class="rounded-full px-4 py-2 text-sm font-semibold border border-lyreco-light-gray/70 bg-lyreco-white/80 hover:bg-lyreco-green/10">
          Long content
        </a>
      </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="rounded-2xl bg-lyreco-dark-white border border-lyreco-light-gray/70 p-4">
        <div class="text-xs font-semibold text-lyreco-dark-gray">Breakpoint</div>
        <div class="mt-1 text-sm font-bold text-lyreco-dark">Resize la fenêtre</div>
      </div>
      <div class="rounded-2xl bg-lyreco-dark-white border border-lyreco-light-gray/70 p-4">
        <div class="text-xs font-semibold text-lyreco-dark-gray">Sticky header</div>
        <div class="mt-1 text-sm font-bold text-lyreco-dark">Scroll + anchors</div>
      </div>
      <div class="rounded-2xl bg-lyreco-dark-white border border-lyreco-light-gray/70 p-4">
        <div class="text-xs font-semibold text-lyreco-dark-gray">Footer</div>
        <div class="mt-1 text-sm font-bold text-lyreco-dark">Toujours en bas</div>
      </div>
    </div>
  </div>

  <!-- Cards -->
  <section id="section-cards" class="scroll-mt-28 space-y-4">
    <div class="flex items-baseline justify-between">
      <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-lyreco-dark">Cards grid</h2>
      <span class="text-xs text-lyreco-dark-gray">Test spacing + wrap</span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php for ($i = 1; $i <= 9; $i++): ?>
        <article class="rounded-3xl border border-lyreco-light-gray/70 bg-lyreco-white/80 shadow-sm p-5">
          <div class="flex items-center justify-between">
            <div class="text-sm font-extrabold text-lyreco-blue">Card <?= $i ?></div>
            <span class="text-[11px] font-semibold text-lyreco-dark-gray">Tag</span>
          </div>
          <p class="mt-3 text-sm text-lyreco-dark-gray">
            Un texte un peu long pour forcer le wrapping et vérifier l’alignement. Lorem ipsum dolor sit amet.
          </p>
          <div class="mt-4 flex flex-wrap gap-2">
            <button class="rounded-full px-4 py-2 text-sm font-semibold bg-lyreco-blue text-lyreco-white hover:bg-lyreco-blue-hover">
              Primary
            </button>
            <button class="rounded-full px-4 py-2 text-sm font-semibold border border-lyreco-light-gray/70 bg-lyreco-white/80 hover:bg-lyreco-green/10">
              Secondary
            </button>
          </div>
        </article>
      <?php endfor; ?>
    </div>
  </section>

  <!-- Table -->
  <section id="section-table" class="scroll-mt-28 space-y-4">
    <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-lyreco-dark">Table</h2>

    <div class="rounded-3xl border border-lyreco-light-gray/70 bg-lyreco-white/80 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-lyreco-dark-white">
            <tr class="text-left text-lyreco-dark-gray">
              <th class="px-5 py-3 font-semibold">ID</th>
              <th class="px-5 py-3 font-semibold">Name</th>
              <th class="px-5 py-3 font-semibold">Status</th>
              <th class="px-5 py-3 font-semibold">Notes</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-lyreco-light-gray/70">
            <?php
            $rows = [
              [101, 'Alpha', 'OK', 'Short'],
              [102, 'Beta', 'Warning', 'A longer note to test wrapping in table cells.'],
              [103, 'Gamma', 'Error', 'Another note'],
              [104, 'Delta', 'OK', '—'],
            ];
            foreach ($rows as [$id, $name, $status, $note]):
              $badge = match ($status) {
                'OK' => 'bg-lyreco-success text-lyreco-white',
                'Warning' => 'bg-lyreco-warning text-lyreco-dark',
                'Error' => 'bg-lyreco-error text-lyreco-white',
                default => 'bg-lyreco-information text-lyreco-white',
              };
            ?>
              <tr class="bg-transparent">
                <td class="px-5 py-3 font-semibold text-lyreco-dark"><?= (int) $id ?></td>
                <td class="px-5 py-3 text-lyreco-dark"><?= htmlspecialchars($name) ?></td>
                <td class="px-5 py-3">
                  <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold <?= $badge ?>">
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>
                <td class="px-5 py-3 text-lyreco-dark-gray"><?= htmlspecialchars($note) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Form -->
  <section id="section-form" class="scroll-mt-28 space-y-4">
    <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-lyreco-dark">Form</h2>

    <form class="rounded-3xl border border-lyreco-light-gray/70 bg-lyreco-white/80 shadow-sm p-6 space-y-5">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <label class="space-y-2">
          <span class="text-sm font-semibold text-lyreco-dark">Email</span>
          <input
            type="email"
            placeholder="name@lyreco.com"
            class="w-full rounded-2xl border border-lyreco-light-gray/70 bg-lyreco-white px-4 py-3 text-sm
                   focus:outline-none focus:ring-2 focus:ring-primary-ring"
          >
        </label>

        <label class="space-y-2">
          <span class="text-sm font-semibold text-lyreco-dark">Subject</span>
          <input
            type="text"
            placeholder="Sujet…"
            class="w-full rounded-2xl border border-lyreco-light-gray/70 bg-lyreco-white px-4 py-3 text-sm
                   focus:outline-none focus:ring-2 focus:ring-primary-ring"
          >
        </label>
      </div>

      <label class="space-y-2 block">
        <span class="text-sm font-semibold text-lyreco-dark">Message</span>
        <textarea
          rows="5"
          placeholder="Tape un message assez long pour tester la hauteur…"
          class="w-full rounded-2xl border border-lyreco-light-gray/70 bg-lyreco-white px-4 py-3 text-sm
                 focus:outline-none focus:ring-2 focus:ring-primary-ring"
        ></textarea>
      </label>

      <div class="flex flex-wrap items-center gap-3">
        <button type="button" class="rounded-full px-5 py-2.5 text-sm font-semibold bg-lyreco-blue text-lyreco-white hover:bg-lyreco-blue-hover">
          Submit (fake)
        </button>
        <button type="button" class="rounded-full px-5 py-2.5 text-sm font-semibold border border-lyreco-light-gray/70 bg-lyreco-white/80 hover:bg-lyreco-green/10">
          Secondary
        </button>
        <p class="text-xs text-lyreco-dark-gray">
          Ici tu vois si les boutons “wrap” correctement sans casser le layout.
        </p>
      </div>
    </form>
  </section>

  <!-- Long content -->
  <section id="section-long" class="scroll-mt-28 space-y-4">
    <h2 class="text-lg sm:text-xl font-extrabold tracking-tight text-lyreco-dark">Long content</h2>

    <div class="rounded-3xl border border-lyreco-light-gray/70 bg-lyreco-white/80 shadow-sm p-6 space-y-4">
      <?php for ($p = 0; $p < 10; $p++): ?>
        <p class="text-sm text-lyreco-dark-gray leading-relaxed">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor,
          dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam.
        </p>
      <?php endfor; ?>

      <div class="rounded-2xl bg-lyreco-dark-white border border-lyreco-light-gray/70 p-4">
        <div class="text-sm font-semibold text-lyreco-dark">Quick checks</div>
        <ul class="mt-2 list-disc pl-5 text-sm text-lyreco-dark-gray space-y-1">
          <li>Le header ne recouvre jamais le contenu (anchors ok grâce à <code>scroll-mt-28</code>)</li>
          <li>La page reste clean en mobile (table scrollable + cards en 1 colonne)</li>
          <li>Le footer colle en bas si contenu court (via flex layout)</li>
        </ul>
      </div>
    </div>
  </section>
</section>
