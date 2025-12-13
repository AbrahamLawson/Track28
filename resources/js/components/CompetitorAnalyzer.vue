<template>
    <div class="competitor-analyzer min-h-screen py-12 relative overflow-hidden">
        <!-- Vertical gradient from white to Track28 colors -->
        <div class="absolute inset-0 bg-gradient-to-b from-white via-indigo-50 to-purple-100 -z-10"></div>
        
        <!-- Enhanced gradient overlay for smoother transition -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-indigo-100/40 to-purple-200/60"></div>
        </div>
        
        <!-- Elegant floating orbs following the gradient -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden opacity-50 -z-10">
            <div class="absolute top-20 right-20 w-[500px] h-[500px] bg-gradient-to-br from-indigo-300/30 to-purple-300/30 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 left-20 w-[600px] h-[600px] bg-gradient-to-tr from-purple-400/40 to-indigo-400/40 rounded-full blur-3xl"></div>
            <div class="absolute bottom-1/4 right-1/3 w-[400px] h-[400px] bg-gradient-to-tl from-violet-300/30 to-indigo-300/30 rounded-full blur-3xl"></div>
        </div>
        
        <!-- Animated subtle shimmer -->
        <div class="absolute inset-0 pointer-events-none opacity-15 -z-10">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-indigo-300/30 to-transparent animate-pulse"></div>
        </div>
        
        <!-- Background Brand Logos -->
        <BrandLogos />

        <!-- Logo Track28 en haut à gauche - Responsive -->
        <div class="fixed top-3 left-3 sm:top-6 sm:left-6 z-20 flex items-center gap-2 sm:gap-4">
            <img :src="logoUrl" alt="Track28 Logo" class="h-8 sm:h-10 md:h-12" />
            <button
                @click="startOnboarding"
                class="flex items-center gap-1 sm:gap-2 px-2 sm:px-4 py-1.5 sm:py-2 bg-white/90 hover:bg-white rounded-full shadow-md hover:shadow-lg transition-all text-xs sm:text-sm font-medium text-indigo-600 hover:text-indigo-700 border border-indigo-200"
                title="Revoir le guide"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="hidden xs:inline sm:inline">Guide</span>
            </button>
        </div>

        <div class="max-w-4xl w-full mx-auto px-4 sm:px-6 relative z-10">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-2 sm:mb-3 text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 text-center mt-16 sm:mt-0">
                Competitor Analysis Tool
            </h1>
            <p class="text-gray-600 mb-6 sm:mb-10 text-center text-sm sm:text-base md:text-lg">Découvrez vos concurrents en quelques secondes</p>

            <!-- Search Form - Responsive -->
            <div class="rounded-xl shadow-lg border border-gray-300 p-4 sm:p-6 mb-6 sm:mb-8" style="background-color: #FAF7F2;">
                <div class="mb-4">
                    <label for="targetUrl" class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        URL du produit ou de la boutique
                    </label>
                    <input
                        id="search-input"
                        v-model="targetUrl"
                        type="url"
                        placeholder="https://example.com/product"
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 text-sm sm:text-base rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 placeholder-gray-400"
                        :disabled="isLoading"
                        @keyup.enter="analyzeCompetitors"
                    />
                </div>

                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                    <button
                        id="search-button"
                        @click="analyzeCompetitors"
                        :disabled="isLoading || !targetUrl"
                        class="flex-1 bg-blue-600 text-white px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer transition-all hover:bg-blue-700"
                    >
                        {{ isLoading ? 'Analyse en cours...' : 'Analyser les concurrents' }}
                    </button>
                    <button
                        v-if="competitors.length > 0"
                        @click="resetAnalysis"
                        class="px-4 sm:px-6 py-2.5 sm:py-3 text-sm sm:text-base rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 cursor-pointer transition-all"
                    >
                        Réinitialiser
                    </button>
                </div>
            </div>

            <!-- Loading Cat GIF with Dynamic Messages -->
            <div v-if="isLoading" class="flex flex-col items-center justify-center py-12 space-y-6">
                <!-- TV Effect Container -->
                <div class="relative">
                    <!-- TV Frame -->
                    <div class="relative bg-gradient-to-br from-gray-900 via-black to-gray-900 p-5 rounded-2xl shadow-2xl border-3 border-gray-800">
                        <!-- Screen with blur background -->
                        <div class="relative h-44 w-60 bg-black rounded-xl overflow-hidden backdrop-blur-xl">
                            <!-- Scan lines effect overlay -->
                            <div class="absolute inset-0 z-10 pointer-events-none opacity-20" style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255,255,255,0.05) 2px, rgba(255,255,255,0.05) 4px);"></div>
                            <!-- Screen glow effect -->
                            <div class="absolute inset-0 z-0 bg-gradient-radial from-gray-200/20 via-transparent to-transparent blur-xl"></div>
                            <!-- GIF -->
                            <img 
                                :src="randomCatGif" 
                                alt="Loading cat" 
                                class="relative z-5 h-full w-full object-contain"
                                @error="handleImageError"
                                @load="handleImageLoad"
                            />
                        </div>
                        <!-- TV Stand -->
                        <div class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 w-16 h-2 bg-gradient-to-b from-gray-800 to-gray-900 rounded-b-lg"></div>
                    </div>
                    <!-- Outer glow -->
                    <div class="absolute inset-0 bg-white/10 blur-2xl -z-10"></div>
                </div>
                <div class="text-center">
                    <p class="text-xl font-semibold text-gray-800 animate-pulse">
                        {{ currentMessage }}
                    </p>
                    <p class="text-sm text-gray-500 mt-2">
                        Cela peut prendre quelques instants...
                    </p>
                </div>
            </div>

            <!-- Error Message -->
            <div
                v-if="error"
                class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6"
            >
                <p class="font-medium">Erreur</p>
                <p class="text-sm">{{ error }}</p>
            </div>

            <!-- Competitors List -->
            <CompetitorList :competitors="competitors" />
        </div>
    </div>
</template>

<script setup>
import { watch, ref, onMounted } from 'vue';
import { useCompetitorAnalysis } from '../composables/useCompetitorAnalysis';
import { useLoadingMessages } from '../composables/useLoadingMessages';
import { useOnboarding } from '../composables/useOnboarding';
import CompetitorList from './CompetitorList.vue';
import BrandLogos from './BrandLogos.vue';

const logoUrl = '/images/logos/track28-logo.svg';

// Liste des GIFs de chat disponibles
const catGifNames = [
    'cat-cat-tongue.gif',
    'cat-dance.gif',
    'cat-kung-fu.gif',
    'cat-pringles.gif',
    'cat-weird-cat-gif.gif'
];

const catGifs = catGifNames.map(name => `${window.location.origin}/images/logos/${name}`);

// GIF de chat sélectionné
const randomCatGif = ref(catGifs[0]);

// Onboarding
const { startOnboarding, startOnboardingIfNeeded } = useOnboarding();

// Précharger tous les GIFs au montage et démarrer l'onboarding
onMounted(() => {
    console.log('Préchargement des GIFs de chat...');
    catGifs.forEach(gifUrl => {
        const img = new Image();
        img.src = gifUrl;
        img.onload = () => console.log('GIF préchargé:', gifUrl);
        img.onerror = () => console.error('Erreur préchargement:', gifUrl);
    });

    // Démarrer l'onboarding si l'utilisateur ne l'a pas encore vu
    startOnboardingIfNeeded();
});

const {
    competitors,
    isLoading,
    error,
    targetUrl,
    analyzeCompetitors,
    resetAnalysis
} = useCompetitorAnalysis();

const { currentMessage, startRotation, stopRotation } = useLoadingMessages();

// Debug pour les images
const handleImageError = (event) => {
    console.error('Erreur de chargement du GIF:', randomCatGif.value);
    console.error('Event:', event);
};

const handleImageLoad = () => {
    console.log('GIF chargé avec succès:', randomCatGif.value);
};

// Gérer la rotation des messages pendant le chargement
watch(isLoading, (newValue) => {
    if (newValue) {
        // Sélectionner un nouveau GIF aléatoire
        const randomIndex = Math.floor(Math.random() * catGifs.length);
        randomCatGif.value = catGifs[randomIndex];
        console.log('Loading started, GIF selected:', randomCatGif.value);
        startRotation();
    } else {
        stopRotation();
    }
});

// Masquer les animations rainbow quand des résultats sont affichés
watch(competitors, (newCompetitors) => {
    const rainbowContainer = document.getElementById('rainbow-container');
    if (rainbowContainer) {
        if (newCompetitors.length > 0) {
            rainbowContainer.classList.add('hide-rainbows');
        } else {
            rainbowContainer.classList.remove('hide-rainbows');
        }
    }
}, { immediate: true });
</script>
