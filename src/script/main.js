//burger-menu
function initBurgerMenu() {
    document.querySelector(".burger-menu").addEventListener("click", () => {
        document.querySelector(".burger-menu").classList.toggle("burger-menu-close");
        document.querySelector(".menu-mobile").classList.toggle("menu-show");
        document.querySelector("body").classList.add("no-scroll");
        document.querySelector("html").classList.add("no-smooth");
    });
    
    document.querySelectorAll(".menu-mobile__item .menu__link").forEach (item => {
        item.addEventListener("click", () => {
            document.querySelector("body").classList.toggle("no-scroll");
            document.querySelector(".burger-menu").classList.toggle("burger-menu-close");
            document.querySelector(".menu-mobile").classList.toggle("menu-show");
            document.querySelector("body").classList.remove("no-scroll");
        });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initBurgerMenu();
});