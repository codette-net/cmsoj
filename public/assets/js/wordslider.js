    document.addEventListener("DOMContentLoaded", () => {
      const words = document.querySelectorAll(".slide-word");
      let index = 0;

      function cycle() {
        words.forEach(w => w.classList.remove("visible"));
        words[index].classList.add("visible");
        index = (index + 1) % words.length;
      }

      cycle();
      setInterval(cycle, 2500);
    });
