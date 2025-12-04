<template>
    <div class="competitor-analyzer min-h-screen overflow-x-hidden py-12">
        <div class="max-w-4xl w-full mx-auto px-6">
            <h1 class="text-3xl font-bold mb-2 text-gray-800 text-center">
                Competitor Analysis Tool
            </h1>
            <p class="text-gray-600 mb-8 text-center">Découvrez vos concurrents en quelques secondes</p>

            <!-- Search Form -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-8">
                <div class="mb-4">
                    <label for="targetUrl" class="block text-sm font-medium text-gray-700 mb-2">
                        URL du produit ou de la boutique
                    </label>
                    <input
                        id="targetUrl"
                        v-model="targetUrl"
                        type="url"
                        placeholder="https://example.com/product"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 placeholder-gray-400"
                        :disabled="isLoading"
                        @keyup.enter="analyzeCompetitors"
                    />
                </div>

                <div class="flex gap-3">
                    <button
                        @click="analyzeCompetitors"
                        :disabled="isLoading || !targetUrl"
                        class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:bg-blue-700"
                    >
                        {{ isLoading ? 'Analyse en cours...' : 'Analyser les concurrents' }}
                    </button>
                    <button
                        v-if="competitors.length > 0"
                        @click="resetAnalysis"
                        class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-all"
                    >
                        Réinitialiser
                    </button>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div v-if="isLoading" class="flex justify-center items-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
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
import { useCompetitorAnalysis } from '../composables/useCompetitorAnalysis';
import CompetitorList from './CompetitorList.vue';

const {
    competitors,
    isLoading,
    error,
    targetUrl,
    analyzeCompetitors,
    resetAnalysis
} = useCompetitorAnalysis();
</script>
