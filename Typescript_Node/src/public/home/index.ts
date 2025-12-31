document.addEventListener("DOMContentLoaded", () => {
  console.log("[layout_test] page script loaded ✅");

  const marker = document.createElement("div");
  marker.textContent = "JS OK ✅";
  marker.setAttribute("data-js-ok", "1");
  marker.style.position = "fixed";
  marker.style.bottom = "16px";
  marker.style.right = "16px";
  marker.style.padding = "8px 10px";
  marker.style.fontSize = "12px";
  marker.style.borderRadius = "12px";
  marker.style.background = "rgba(0,0,0,.75)";
  marker.style.color = "white";
  marker.style.zIndex = "9999";
  document.body.appendChild(marker);

  setTimeout(() => marker.remove(), 2500);
});
