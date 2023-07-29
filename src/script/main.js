//burger-menu

function initBurgerMenu() {
    const burgerMenu = document.querySelector(".burger-menu");
    const menuMobile = document.querySelector(".menu-mobile");

    document.querySelector(".burger-menu").addEventListener("click", () => {
        burgerMenu.classList.toggle("burger-menu-close");
        menuMobile.classList.toggle("menu-show");
        document.querySelector("body").classList.add("no-scroll");
        document.querySelector("html").classList.add("no-smooth");
    });
    
    document.querySelectorAll(".menu-mobile__item .menu__link").forEach (item => {
        item.addEventListener("click", () => {
            document.querySelector("body").classList.toggle("no-scroll");
            burgerMenu.classList.toggle("burger-menu-close");
            menuMobile.classList.toggle("menu-show");
            document.querySelector("body").classList.remove("no-scroll");
        });
    });
}

document.addEventListener("DOMContentLoaded", () => {
    initBurgerMenu();
});