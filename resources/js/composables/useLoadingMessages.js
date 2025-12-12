import { ref, onUnmounted } from 'vue';

/**
 * Composable pour afficher des messages dynamiques pendant le chargement
 */
export function useLoadingMessages() {
    const messages = [
        "🔍 Analyse en cours...",
        "🌐 Nous recherchons les meilleurs concurrents...",
        "📊 Exploration du marché...",
        "🎯 Identification des acteurs clés...",
        "💡 Collecte des informations...",
        "🚀 Analyse des tendances...",
        "⚡ Bientôt terminé...",
        "🔎 Découverte de nouvelles opportunités...",
        "📈 Évaluation de la concurrence...",
        "✨ Finalisation de l'analyse..."
    ];

    const currentMessage = ref(messages[0]);
    let intervalId = null;
    let currentIndex = 0;

    /**
     * Démarre la rotation des messages
     */
    const startRotation = (intervalMs = 2500) => {
        currentIndex = 0;
        currentMessage.value = messages[0];

        intervalId = setInterval(() => {
            currentIndex = (currentIndex + 1) % messages.length;
            currentMessage.value = messages[currentIndex];
        }, intervalMs);
    };

    /**
     * Arrête la rotation des messages
     */
    const stopRotation = () => {
        if (intervalId) {
            clearInterval(intervalId);
            intervalId = null;
        }
    };

    /**
     * Nettoie l'intervalle lors du démontage du composant
     */
    onUnmounted(() => {
        stopRotation();
    });

    return {
        currentMessage,
        startRotation,
        stopRotation
    };
}
