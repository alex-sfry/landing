//burger-menu
document.querySelector(".burger-menu").addEventListener("click", () => {
	document.querySelector(".burger-menu").classList.toggle("burger-menu-close");
	document.querySelector(".menu").classList.toggle("menu-show");
	document.querySelector("body").classList.toggle("no-scroll");
});
document.querySelectorAll(".menu-link").forEach (item => {
	item.addEventListener("click", () => {
		document.querySelector("body").classList.toggle("no-scroll");
		document.querySelector(".burger-menu").classList.toggle("burger-menu-close");
		document.querySelector(".menu").classList.toggle("menu-show");		
	});
});


document.addEventListener("DOMContentLoaded", () => {

});