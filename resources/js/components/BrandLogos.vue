<template>
    <div class="brand-logos-background">
        <img v-for="(brand, index) in allBrands" :key="index"
             :src="brand.logo"
             :alt="brand.name"
             class="brand-logo"
             :style="getLogoStyle(index)"
             loading="eager"
             @error="handleImageError" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

// Les 9 logos de base
const baseBrands = [
    { name: 'Athleta', logo: '/images/logos/Athleta-logo.jpg' },
    { name: 'Gymshark', logo: '/images/logos/Gymshark-Logo-Designer-1.jpg' },
    { name: 'Maison Regard Beauté', logo: '/images/logos/Logo-Maison-regard-beaute-e1717775034953.webp' },
    { name: 'Huda Beauty', logo: '/images/logos/huda-beauty-logo.webp' },
    { name: 'Sephora', logo: '/images/logos/logo-Sephora.jpg' },
    { name: 'Alo Yoga', logo: '/images/logos/logo-alo.jpg' },
    { name: 'Lululemon', logo: '/images/logos/lululemon-logo.jpg' },
    { name: 'Shopify', logo: '/images/logos/shopify-logo-0.png' },
    { name: 'Zara', logo: '/images/logos/zara-logo-0.png' }
];

// Dupliquer les logos pour remplir l'écran (répéter 2 fois pour un meilleur espacement)
const allBrands = computed(() => {
    const repeated = [];
    for (let i = 0; i < 2; i++) {
        repeated.push(...baseBrands);
    }
    return repeated;
});

const getLogoStyle = (index) => {
    // Positions sur les côtés uniquement pour éviter le loading au centre
    const positions = [
        { top: '50%', left: '5%' }, { top: '50%', left: '15%' }, { top: '50%', left: '75%' }, { top: '50%', left: '85%' },
        { top: '60%', left: '8%' }, { top: '60%', left: '20%' }, { top: '60%', left: '80%' }, { top: '60%', left: '92%' },
        { top: '70%', left: '12%' }, { top: '70%', left: '25%' }, { top: '70%', left: '72%' }, { top: '70%', left: '88%' },
        { top: '80%', left: '5%' }, { top: '80%', left: '18%' }, { top: '80%', left: '78%' }, { top: '80%', left: '95%' },
        { top: '90%', left: '10%' }, { top: '90%', left: '22%' }, { top: '90%', left: '75%' }, { top: '90%', left: '90%' },
        { top: '100%', left: '7%' }, { top: '100%', left: '15%' }, { top: '100%', left: '82%' }, { top: '100%', left: '93%' },
        { top: '110%', left: '12%' }, { top: '110%', left: '25%' }, { top: '110%', left: '70%' }, { top: '110%', left: '85%' },
        { top: '120%', left: '5%' }, { top: '120%', left: '20%' }, { top: '120%', left: '77%' }, { top: '120%', left: '92%' },
        { top: '130%', left: '8%' }, { top: '130%', left: '18%' }, { top: '130%', left: '80%' }, { top: '130%', left: '88%' },
        { top: '140%', left: '10%' }, { top: '140%', left: '25%' }, { top: '140%', left: '75%' }, { top: '140%', left: '95%' }
    ];

    return positions[index % positions.length];
};

const handleImageError = (event) => {
    // Si le logo ne charge pas, cacher l'image
    event.target.style.display = 'none';
};
</script>

<style scoped>
.brand-logos-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
    z-index: 1;
}

.brand-logo {
    position: absolute;
    width: 60px;
    height: 60px;
    object-fit: contain;
    opacity: 0.8;
    user-select: none;
    transform: rotate(-15deg);
    filter: grayscale(30%) brightness(1.1);
    transition: opacity 0.3s ease, filter 0.3s ease;
}

/* Variation de tailles */
.brand-logo:nth-child(3n) {
    width: 50px;
    height: 50px;
    opacity: 0.7;
}

.brand-logo:nth-child(5n) {
    width: 70px;
    height: 70px;
    opacity: 0.9;
}

.brand-logo:nth-child(7n) {
    transform: rotate(15deg);
}

.brand-logo:nth-child(11n) {
    transform: rotate(-25deg);
}

.brand-logo:nth-child(13n) {
    transform: rotate(0deg);
}

/* Responsive */
@media (max-width: 768px) {
    .brand-logo {
        width: 40px;
        height: 40px;
    }

    .brand-logo:nth-child(3n) {
        width: 35px;
        height: 35px;
    }

    .brand-logo:nth-child(5n) {
        width: 45px;
        height: 45px;
    }
}
</style>
