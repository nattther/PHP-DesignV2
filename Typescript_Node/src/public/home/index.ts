document.addEventListener("DOMContentLoaded", () => {
  const btn = document.querySelector<HTMLButtonElement>("[data-burger]");
  const menu = document.querySelector<HTMLElement>("[data-menu]");

  if (!btn || !menu) return;

  btn.addEventListener("click", () => {
    menu.classList.toggle("hidden");
  });
});
