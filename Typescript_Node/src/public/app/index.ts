function setExpanded(btn: HTMLButtonElement, panel: HTMLElement, expanded: boolean) {
  btn.setAttribute("aria-expanded", expanded ? "true" : "false");
  panel.hidden = !expanded;
}

document.addEventListener("DOMContentLoaded", () => {
  const btn = document.querySelector<HTMLButtonElement>("[data-nav-toggle]");
  const panel = document.querySelector<HTMLElement>("[data-nav-panel]");

  if (!btn || !panel) return;

  const close = () => setExpanded(btn, panel, false);

  btn.addEventListener("click", () => {
    const expanded = btn.getAttribute("aria-expanded") === "true";
    setExpanded(btn, panel, !expanded);
  });

  // ESC closes
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") close();
  });

  // Click outside closes
  document.addEventListener("click", (e) => {
    const t = e.target as Node;
    if (!panel.hidden && !panel.contains(t) && !btn.contains(t)) close();
  });

  // Click on a link closes
  panel.addEventListener("click", (e) => {
    const el = e.target as HTMLElement;
    if (el.closest("a")) close();
  });
});
