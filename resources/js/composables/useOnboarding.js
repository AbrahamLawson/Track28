import { driver } from 'driver.js';
import 'driver.js/dist/driver.css';

/**
 * Composable pour gérer l'onboarding de l'application
 */
export function useOnboarding() {
    const ONBOARDING_KEY = 'track28_onboarding_completed';

    /**
     * Vérifie si l'utilisateur a déjà vu l'onboarding
     */
    const hasSeenOnboarding = () => {
        return localStorage.getItem(ONBOARDING_KEY) === 'true';
    };

    /**
     * Marque l'onboarding comme complété
     */
    const markOnboardingComplete = () => {
        localStorage.setItem(ONBOARDING_KEY, 'true');
    };

    /**
     * Réinitialise l'onboarding (pour le revoir)
     */
    const resetOnboarding = () => {
        localStorage.removeItem(ONBOARDING_KEY);
    };

    /**
     * Détecte si on est sur mobile
     */
    const isMobile = () => {
        return window.innerWidth < 768;
    };

    /**
     * Crée et démarre le tour d'onboarding
     */
    const startOnboarding = () => {
        const mobile = isMobile();

        const driverObj = driver({
            showProgress: true,
            showButtons: ['next', 'previous', 'close'],
            nextBtnText: mobile ? 'Suivant' : 'Suivant →',
            prevBtnText: mobile ? 'Retour' : '← Précédent',
            doneBtnText: mobile ? 'OK' : 'Terminer ✓',
            closeBtnText: 'Passer',
            progressText: '{{current}}/{{total}}',
            popoverClass: mobile ? 'driver-popover-mobile' : 'driver-popover-desktop',
            steps: [
                {
                    element: '#search-input',
                    popover: {
                        title: '🎯 Bienvenue sur Track28!',
                        description: 'Track28 vous aide à analyser vos concurrents en quelques secondes. Commencez par entrer l\'URL d\'un produit ou d\'un site e-commerce.',
                        side: 'bottom',
                        align: 'center'
                    }
                },
                {
                    element: '#search-button',
                    popover: {
                        title: '🚀 Lancer l\'analyse',
                        description: 'Une fois l\'URL saisie, cliquez sur ce bouton pour démarrer l\'analyse. Notre IA va identifier automatiquement vos principaux concurrents.',
                        side: 'left',
                        align: 'start'
                    }
                },
                {
                    popover: {
                        title: '⏱️ Temps d\'analyse',
                        description: 'L\'analyse prend généralement entre 10 et 30 secondes. Track28 identifie les meilleurs concurrents dans votre niche.',
                        side: 'top',
                        align: 'center'
                    }
                },
                {
                    popover: {
                        title: '📋 Export et sauvegarde',
                        description: 'Une fois l\'analyse terminée, vous pourrez copier les résultats ou les sauvegarder pour vos rapports de veille concurrentielle.',
                        side: 'top',
                        align: 'center'
                    }
                },
                {
                    popover: {
                        title: '🎉 C\'est parti !',
                        description: 'Vous êtes prêt à découvrir vos concurrents ! Essayez maintenant avec l\'URL d\'un de vos produits ou de votre boutique.',
                        side: 'top',
                        align: 'center'
                    }
                }
            ],
            onDestroyStarted: () => {
                // Marquer comme complété quand l'utilisateur ferme le tour
                markOnboardingComplete();
                driverObj.destroy();
            },
        });

        driverObj.drive();
    };

    /**
     * Démarre l'onboarding automatiquement si l'utilisateur ne l'a pas encore vu
     */
    const startOnboardingIfNeeded = () => {
        if (!hasSeenOnboarding()) {
            // Vérifier si la popup MailerLite a déjà été complétée
            const mailerliteCompleted = localStorage.getItem('mailerlite_popup_completed') === 'true';

            if (mailerliteCompleted) {
                // Si MailerLite déjà complété, démarrer l'onboarding directement
                setTimeout(() => {
                    startOnboarding();
                }, 800);
            } else {
                // Sinon, attendre l'événement de complétion de MailerLite
                window.addEventListener('mailerlite-completed', () => {
                    setTimeout(() => {
                        startOnboarding();
                    }, 1000); // 1 seconde après la fermeture de la popup
                }, { once: true }); // N'écouter qu'une seule fois
            }
        }
    };

    return {
        hasSeenOnboarding,
        markOnboardingComplete,
        resetOnboarding,
        startOnboarding,
        startOnboardingIfNeeded
    };
}
