    const imgScroller = document.querySelector(".img-scroller");

    function mainScroller() {
      imgScroller.querySelectorAll(".image-scroll").forEach((img, index) => {

        if (index === 0) {
          img.classList.add("active");
        }
      });

      // scroll trough images
      let i = 0;
      setInterval(() => {
        imgScroller.querySelector(".active").classList.remove("active");
        i = (i + 1) % imgScroller.children.length;
        imgScroller.children[i].classList.add("active");
      }, 5000);
    }
    document.addEventListener("DOMContentLoaded", mainScroller);