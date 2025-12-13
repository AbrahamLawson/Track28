<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Track28 - Competitor Analysis Tool</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/images/logos/track28-icon.svg">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- MailerLite Universal -->
        <script>
            (function(w,d,e,u,f,l,n){w[f]=w[f]||function(){(w[f].q=w[f].q||[])
            .push(arguments);},l=d.createElement(e),l.async=1,l.src=u,
            n=d.getElementsByTagName(e)[0],n.parentNode.insertBefore(l,n);})
            (window,document,'script','https://assets.mailerlite.com/js/universal.js','ml');
            ml('account', '1946425');
        </script>
        <!-- End MailerLite Universal -->

        <!-- MailerLite Popup Configuration -->
        <script>
            // Désactiver la fermeture de la popup MailerLite
            window.addEventListener('load', function() {
                // Fonction pour retirer les boutons de fermeture
                function removeCloseButtons() {
                    // Toutes les variations possibles de sélecteurs pour le bouton de fermeture
                    const closeSelectors = [
                        '.ml-popup-close',
                        '.ml-form-close',
                        '[data-ml-close]',
                        'button[aria-label*="Close"]',
                        'button[aria-label*="close"]',
                        '.ml-form-close-button',
                        '[class*="close"]',
                        'iframe .ml-popup-close', // Dans le cas d'une iframe
                        'svg.h-5.w-5.fill-current', // SVG spécifique de la croix
                        '.h-5.w-5.fill-current', // Classes de la croix
                        'button svg.fill-current', // SVG dans un bouton
                        'svg[viewBox="0 0 41.756 41.756"]' // ViewBox spécifique de la croix
                    ];

                    closeSelectors.forEach(selector => {
                        document.querySelectorAll(selector).forEach(el => {
                            el.remove(); // Retirer complètement du DOM
                        });
                    });

                    // Retirer aussi le bouton parent du SVG
                    document.querySelectorAll('button').forEach(button => {
                        const svg = button.querySelector('svg.h-5.w-5.fill-current');
                        if (svg) {
                            button.remove();
                        }
                    });

                    // Empêcher le clic sur l'overlay
                    const overlay = document.querySelector('.ml-popup-overlay');
                    if (overlay) {
                        overlay.style.pointerEvents = 'none';
                    }

                    // Garder la popup cliquable
                    const popup = document.querySelector('.ml-popup-window');
                    if (popup) {
                        popup.style.pointerEvents = 'auto';
                    }
                }

                // CSS pour masquer tout bouton de fermeture
                const style = document.createElement('style');
                style.innerHTML = `
                    /* Masquer tous les types de boutons de fermeture */
                    .ml-popup-close,
                    .ml-form-close,
                    [data-ml-close],
                    button[aria-label*="Close"],
                    button[aria-label*="close"],
                    .ml-form-close-button,
                    [class*="ml"][class*="close"],
                    svg.h-5.w-5.fill-current,
                    .h-5.w-5.fill-current,
                    button svg.fill-current,
                    svg[viewBox="0 0 41.756 41.756"] {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        pointer-events: none !important;
                    }
                    /* Masquer le bouton parent du SVG */
                    button:has(svg.h-5.w-5.fill-current),
                    button:has(svg[viewBox="0 0 41.756 41.756"]) {
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        pointer-events: none !important;
                    }
                    /* Empêcher le clic sur l'overlay */
                    .ml-popup-overlay {
                        pointer-events: none !important;
                    }
                    /* Garder la popup cliquable */
                    .ml-popup-window {
                        pointer-events: auto !important;
                    }
                    /* Bloquer la touche ESC */
                    body.ml-popup-open {
                        overflow: hidden !important;
                    }
                `;
                document.head.appendChild(style);

                // Retirer les boutons plusieurs fois pour être sûr
                setTimeout(removeCloseButtons, 500);
                setTimeout(removeCloseButtons, 1000);
                setTimeout(removeCloseButtons, 1500);
                setTimeout(removeCloseButtons, 2000);

                // Observer pour retirer les boutons ajoutés dynamiquement
                const observer = new MutationObserver(function(mutations) {
                    removeCloseButtons();
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });

                // Bloquer la touche ESC
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' || e.key === 'Esc') {
                        const popup = document.querySelector('.ml-popup-window');
                        if (popup && popup.style.display !== 'none') {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    }
                }, true);

                // Écouter l'événement de soumission du formulaire MailerLite
                window.addEventListener('message', function(event) {
                    // Vérifier si c'est un événement MailerLite
                    if (event.data && typeof event.data === 'string') {
                        try {
                            const data = JSON.parse(event.data);
                            // Quand le formulaire est soumis avec succès
                            if (data.type === 'ml:success' || data.action === 'submit') {
                                // Stocker un flag pour indiquer que la popup a été complétée
                                localStorage.setItem('mailerlite_popup_completed', 'true');
                                // Déclencher un événement personnalisé pour l'onboarding
                                window.dispatchEvent(new CustomEvent('mailerlite-completed'));
                            }
                        } catch (e) {
                            // Ignorer les messages non-JSON
                        }
                    }
                });
            });
        </script>
    </head>
    <body class="min-h-screen cyber-background">
        <!-- Rainbow Animation Background -->
        <div id="rainbow-container">
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="rainbow"></div>
            <div class="h"></div>
            <div class="v"></div>
        </div>

        <div id="app"></div>
    </body>
</html>
