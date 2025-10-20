document.addEventListener("DOMContentLoaded", function () {
  /**
   * Animasi elemen saat di-scroll ke dalam viewport menggunakan Intersection Observer.
   * Elemen yang ingin dianimasikan harus memiliki class `.animate-on-scroll`.
   */
  const animatedElements = document.querySelectorAll(".animate-on-scroll");

  // Cek apakah browser mendukung IntersectionObserver
  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      (entries, observer) => {
        entries.forEach((entry) => {
          // Jika elemen masuk ke viewport
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            // Hentikan observasi setelah animasi berjalan agar lebih efisien
            observer.unobserve(entry.target);
          }
        });
      },
      {
        threshold: 0.1, // Animasi berjalan saat 10% elemen terlihat
      }
    );

    animatedElements.forEach((element) => {
      observer.observe(element);
    });
  } else {
    // Fallback untuk browser lama yang tidak mendukung IntersectionObserver
    // Langsung tampilkan semua elemen tanpa animasi.
    animatedElements.forEach((element) => {
      element.classList.add("is-visible");
    });
  }

  /**
   * Efek partikel neon yang mengikuti kursor (opsional, untuk sentuhan futuristik).
   * Anda bisa mengaktifkan/menonaktifkan ini dengan mudah.
   * Untuk saat ini, kita fokus pada animasi scroll.
   * Jika ingin menambahkan efek kursor, kode bisa ditambahkan di sini.
   */
});

/* -------------------------------------------------------------------------- */
/*                        Efek Partikel Neon Mengikuti Kursor                   */
/* -------------------------------------------------------------------------- */

const canvas = document.createElement("canvas");
document.body.appendChild(canvas);
const ctx = canvas.getContext("2d");

canvas.style.position = "fixed";
canvas.style.top = "0";
canvas.style.left = "0";
canvas.style.width = "100%";
canvas.style.height = "100%";
canvas.style.pointerEvents = "none"; // Agar kanvas tidak menghalangi interaksi
canvas.style.zIndex = "9999";

let particles = [];
const mouse = { x: null, y: null };

window.addEventListener("mousemove", (event) => {
  mouse.x = event.x;
  mouse.y = event.y;
  for (let i = 0; i < 3; i++) {
    particles.push(new Particle());
  }
});

class Particle {
  constructor() {
    this.x = mouse.x;
    this.y = mouse.y;
    this.size = Math.random() * 2 + 1;
    this.speedX = Math.random() * 2 - 1;
    this.speedY = Math.random() * 2 - 1;
    this.color = `hsl(${Math.random() * 60 + 180}, 100%, 70%)`; // Nuansa Cyan
  }
  update() {
    this.x += this.speedX;
    this.y += this.speedY;
    if (this.size > 0.1) this.size -= 0.03;
  }
  draw() {
    ctx.fillStyle = this.color;
    ctx.beginPath();
    ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
    ctx.fill();
  }
}

function handleParticles() {
  for (let i = 0; i < particles.length; i++) {
    particles[i].update();
    particles[i].draw();
    if (particles[i].size <= 0.1) {
      particles.splice(i, 1);
      i--;
    }
  }
}

function animate() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  handleParticles();
  requestAnimationFrame(animate);
}

function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}

window.addEventListener('resize', resizeCanvas);
resizeCanvas();
animate();