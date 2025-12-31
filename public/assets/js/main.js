// made by Jos Velema 2022 , redesigned in 2025
const navBtn = document.querySelector("#menu-btn");
const nav = document.querySelector("nav");
const navOffset = nav.offsetTop;
const navInfo = document.querySelector(".nav-info");

const main = document.querySelector("main");
const navLinks = document.querySelector(".nav-links");
const navLink = document.querySelectorAll(".nav-link");

const logo = document.querySelector(".logo");
const menuBtn = document.querySelector(".btn--menu");


// active page
// console.log(window.location.href)
navLink.forEach((link) => {
    // console.log((link.href, window.location.href));
    console.log(link.href);

    // get current url without the # or ?
    const currentURL = window.location.href.split("#")[0].split("?")[0];
    if (link.href === currentURL) {
        link.setAttribute("aria-current", "page");
        link.setAttribute("title", "You are currently on this page");
    }
});

navBtn.addEventListener("click", () => {
    navLinks.classList.add("activated");
    const isExpanded = JSON.parse(navBtn.getAttribute("aria-expanded"));
    navBtn.setAttribute("aria-expanded", !isExpanded);

    !isExpanded && nav.classList.add("active");
});

window.addEventListener("scroll", () => {
    if (window.scrollY > navOffset) {
        nav.classList.add("scrolled");
    } else {
        nav.classList.remove("scrolled");
    }
});

const fadeUpObserver = new IntersectionObserver(
    (elsToWatch) => {
        elsToWatch.forEach((el) => {
            if (el.isIntersecting) {
                el.target.classList.add("faded");
                fadeUpObserver.unobserve(el.target);
            }
        });
    },
    { threshold: 0.05 }
);

document.querySelectorAll(".fade-up").forEach((item) => {
    console.log(item);
    fadeUpObserver.observe(item);
});

document.querySelectorAll(".fade-up-delay").forEach((item) => {
    console.log(item);
    fadeUpObserver.observe(item);
});

// window.onscroll = function () {
//     var e = document.getElementById("scrolltop");
//     if (!e) {
//         e = document.createElement("a");
//         e.id = "scrolltop";
//         e.href = "#";
//         e.title = "Back to top";
//         document.body.appendChild(e);
//     }
//     e.style.display =
//         document.documentElement.scrollTop > 300 ? "block" : "none";
//     e.onclick = (ev) => {
//         ev.preventDefault();
//         document.documentElement.scrollTop = 0;
//     };
// };
