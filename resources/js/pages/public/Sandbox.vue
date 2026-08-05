<template>
  <div class="card-wrapper">
    <div class="card">
      <div class="card-top">
        <div class="avatar">
          <span class="avatar-ring"></span>
          <img 
            src="https://cdn.phototourl.com/free/2026-07-08-80d7b3be-ffd5-440a-9390-8bde9aab4c7b.png" 
            alt="Ing. Yumy Romero Talavera" 
            class="avatar-image" 
          />
        </div>
      </div>

      <div class="card-bottom">
        <h2 class="name">Ing. Yumy Romero Talavera</h2>
        <p class="role">Jefa de Sub Unidad</p>
        <p class="subunit">Sub Unidad de Seguimiento y Desarrollo del Graduado</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Ya no necesitamos definir props porque hemos puesto el texto y la imagen fijos directamente en el HTML de arriba.
</script>

<style scoped>
/* Contenedor principal: fondo verde claro con textura sutil */
.card-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #f0fdf4 0%, #d8f3dc 100%);
  padding: 30px;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  position: relative;
}

.card-wrapper::before {
  content: "";
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(45, 106, 79, 0.08) 1px, transparent 1px);
  background-size: 26px 26px;
  pointer-events: none;
  opacity: 0.6;
}

/* ===== TARJETA PRINCIPAL ===== */
.card {
  position: relative;
  width: 270px;
  height: 380px;
  border-radius: 28px;
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.7);
  box-shadow: 0 15px 35px rgba(27, 67, 50, 0.08);
  overflow: hidden;
  transform-style: preserve-3d;
  perspective: 800px;
  transition: transform 0.45s cubic-bezier(0.175, 0.885, 0.32, 1.275),
              box-shadow 0.45s ease,
              border-color 0.45s ease;
  opacity: 0;
  transform: translateY(50px) scale(0.92) rotateX(6deg);
  animation: fadeUp 0.9s forwards cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* ===== BORDE CON GLOW ESTÁTICO ===== */
.card::before {
  content: "";
  position: absolute;
  inset: -2px;
  border-radius: 30px;
  padding: 2px;
  background: linear-gradient(135deg, rgba(82, 183, 136, 0.3), rgba(45, 106, 79, 0.1));
  -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  opacity: 0.5;
  transition: opacity 0.6s ease, background 0.6s ease;
  pointer-events: none;
}

.card:hover::before {
  opacity: 1;
  background: linear-gradient(135deg, rgba(82, 183, 136, 0.6), rgba(45, 106, 79, 0.3));
}

.card:hover {
  transform: translateY(-14px) scale(1.02) rotateX(2deg);
  box-shadow: 0 30px 50px rgba(45, 106, 79, 0.15), 0 0 30px rgba(82, 183, 136, 0.08);
  border-color: rgba(149, 213, 178, 0.5);
}

/* ===== AVATAR CON IMAGEN REAL ===== */
.card-top {
  display: flex;
  justify-content: center;
  padding-top: 25px;
  width: 100%;
  flex-shrink: 0;
  z-index: 1;
}

.avatar {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end;
  height: 220px;
  width: 100%;
  animation: floatIdle 4.5s ease-in-out infinite;
}

.avatar-image {
  position: relative;
  height: 100%;
  width: auto;
  max-width: 260px;
  object-fit: contain;
  object-position: bottom;
  z-index: 2;
  transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  filter: drop-shadow(0 15px 20px rgba(27, 67, 50, 0.15));
}

.card:hover .avatar-image {
  transform: scale(1.08) translateY(-5px);
}

.avatar-ring {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 2px solid rgba(82, 183, 136, 0.4);
  opacity: 0;
  transform: translate(-50%, -50%) scale(0.8);
  transition: opacity 0.35s ease, transform 0.35s ease;
  z-index: 1;
}

.card:hover .avatar-ring {
  opacity: 1;
  transform: translate(-50%, -50%) scale(1);
  animation: pulseRing 1.8s ease-out infinite;
}

/* ===== BARRA INFERIOR ===== */
.card-bottom {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 150px; 
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(240, 253, 244, 0.88));
  backdrop-filter: blur(30px);
  -webkit-backdrop-filter: blur(30px);
  border-top: 1.5px solid rgba(255, 255, 255, 1);
  border-radius: 30px 30px 28px 28px;
  box-shadow: 0 -10px 30px rgba(8, 28, 21, 0.06);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  text-align: center;
  padding: 0 20px; 
  padding-bottom: 10px; 
  box-sizing: border-box;
  transition: background 0.3s ease;
  z-index: 3;
}

.name {
  margin: 0 0 6px 0;
  font-size: 1.15rem; 
  font-weight: 700;
  color: #081c15;
  letter-spacing: -0.3px;
  line-height: 1.2;
  transition: transform 0.3s ease;
}

.card:hover .name {
  transform: translateY(-2px);
}

.role {
  margin: 0 0 4px 0;
  font-size: 0.9rem;
  font-weight: 700;
  color: #2d6a4f; 
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.subunit {
  margin: 0;
  font-size: 0.78rem;
  font-weight: 500;
  color: #52b788; 
  line-height: 1.3;
  max-width: 95%; 
}

/* ===== ANIMACIONES SUAVES ===== */
@keyframes fadeUp {
  0% {
    opacity: 0;
    transform: translateY(50px) scale(0.92) rotateX(6deg);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1) rotateX(0deg);
  }
}

@keyframes floatIdle {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-8px); }
}

@keyframes pulseRing {
  0% { box-shadow: 0 0 0 0 rgba(82, 183, 136, 0.35); }
  100% { box-shadow: 0 0 0 16px rgba(82, 183, 136, 0); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 480px) {
  .card { width: 280px; height: 380px; }
  .card-top { padding-top: 15px; }
  .avatar { height: 180px; }
  .avatar-ring { width: 130px; height: 130px; }
  .card-bottom { height: 155px; border-radius: 24px; padding-bottom: 5px; }
  .name { font-size: 1.05rem; }
  .role { font-size: 0.85rem; }
  .subunit { font-size: 0.75rem; }
}

@media (max-width: 360px) {
  .card { width: 240px; height: 340px; }
  .avatar { height: 150px; }
  .avatar-ring { width: 110px; height: 110px; }
  .card-bottom { height: 140px; padding: 0 12px; }
  .name { font-size: 0.95rem; }
  .role { font-size: 0.8rem; }
  .subunit { font-size: 0.7rem; }
}

@media (prefers-reduced-motion: reduce) {
  .card, .avatar, .avatar-ring, .avatar-image, .card::before { animation: none !important; transition: none !important; }
  .card { opacity: 1; transform: none; }
  .card::before { opacity: 0.5; }
  .card:hover::before { opacity: 1; }
}
</style>