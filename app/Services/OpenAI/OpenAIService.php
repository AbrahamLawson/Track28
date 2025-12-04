<?php

declare(strict_types=1);

namespace App\Services\OpenAI;

use App\Services\WebScraper\WebScraperService;
use OpenAI\Laravel\Facades\OpenAI;

/**
 * Service to interact with OpenAI API
 */
final class OpenAIService
{
    public function __construct(
        private readonly WebScraperService $webScraperService
    ) {}

    public function searchCompetitors(string $targetUrl): string
    {
        // Scrape the target URL to get product information
        $scrapedData = $this->webScraperService->scrapeProductPage($targetUrl);

        $prompt = $this->buildSearchPrompt($targetUrl, $scrapedData);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert in e-commerce competitive analysis. You must return only valid JSON without any additional text.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ]);

        return $response->choices[0]->message->content;
    }

    private function buildSearchPrompt(string $targetUrl, array $scrapedData): string
    {
        // Format scraped data for the prompt
        $productInfo = $this->formatScrapedData($scrapedData);

        return <<<PROMPT
        **ÉTAPE 1 - ANALYSE DU PRODUIT CIBLE :**
        Analyse attentivement les informations extraites de la page produit pour identifier PRÉCISÉMENT le type de produit ou la catégorie concernée.

        **Lien de la boutique ou du produit cible :** {$targetUrl}

        **INFORMATIONS EXTRAITES DE LA PAGE :**
        {$productInfo}

        **IMPORTANT :** Utilise les informations ci-dessus pour identifier le VRAI produit (gourde, vêtement, accessoire, etc.) et cherche des concurrents UNIQUEMENT dans cette catégorie spécifique.

        **ÉTAPE 2 - CRITÈRES DE SÉLECTION DES CONCURRENTS :**

        **Type de marques recherchées :**
        - Marques spécialisées dans la MÊME catégorie de produit que celle identifiée dans l'URL
        - Marques opérant principalement en ligne (DNVB - Digital Native Vertical Brands de préférence)
        - ÉVITE les géants généralistes multi-catégories (Amazon, Nike, Decathlon, H&M, etc.)
        - Focus sur des marques de niche ou spécialisées dans ce type de produit

        **Pertinence des produits :**
        - Les concurrents doivent vendre le MÊME type de produit que celui du lien cible
        - Produits comparables en termes de fonctionnalité et d'usage
        - Positionnement prix similaire ou légèrement différent (gamme haute/basse acceptable)

        **Taille et notoriété :**
        - Priorise les marques établies avec une vraie présence en ligne
        - Taille moyenne à grande dans leur niche spécifique
        - Notoriété vérifiable (réseaux sociaux, avis clients, présence médiatique)

        **Nombre de résultats :**
        - Fournis EXACTEMENT 10 concurrents, ni plus ni moins

        **ÉTAPE 3 - INFORMATIONS À FOURNIR POUR CHAQUE CONCURRENT :**

        - **name** : Nom de la marque ou boutique
        - **product_url** : URL STABLE de la marque - page d'accueil OU page de catégorie de produits similaires. JAMAIS un lien vers un produit spécifique qui pourrait expirer
        - **price** : Prix moyen de leurs produits similaires (nombre décimal, null si non disponible)
        - **description** : Description concise du produit concurrent comparable
        - **positioning** : Positionnement marketing (exemples: "Premium", "Écoresponsable", "Innovant", "Milieu de gamme", "Accessible", "Performance", "Design")
        - **strengths** : Points forts spécifiques et différenciateurs de cette marque
        - **notoriety** : Indicateurs de notoriété (followers réseaux sociaux, présence médiatique, communauté)
        - **social_media** : Array contenant les réseaux sociaux de la marque avec le format suivant :
          [
            {
              "platform": "Instagram" | "Facebook" | "TikTok" | "Twitter" | "LinkedIn" | "YouTube",
              "url": "URL complète du profil"
            }
          ]
          IMPORTANT: Ne pas inclure le nombre d'abonnés, on va les récupérer automatiquement.
          Si aucun réseau social n'est trouvé, retourner un tableau vide []

        **ÉTAPE 4 - RÈGLES STRICTES :**
        - ✅ Analyse l'URL cible pour comprendre la catégorie EXACTE du produit
        - ✅ Ne fournis QUE des concurrents vendant le MÊME type de produit
        - ✅ Vérifie que chaque concurrent est réellement pertinent
        - ❌ EXCLUS les marketplaces (Amazon, eBay, AliExpress, etc.)
        - ❌ EXCLUS les grandes enseignes généralistes
        - ❌ JAMAIS de liens vers des produits spécifiques - UNIQUEMENT homepage ou collections
        - ❌ Ne confonds pas les catégories de produits (ex: si c'est une gourde, ne donne PAS de marques de vêtements)

        **FORMAT DE RÉPONSE :**

        Retourne UNIQUEMENT un objet JSON avec cette structure exacte :
        {
            "competitors": [
                {
                    "name": "Nom de la marque",
                    "product_url": "https://example.com",
                    "price": 29.99,
                    "description": "Description du produit concurrent",
                    "positioning": "Positionnement marketing",
                    "strengths": "Points forts différenciateurs",
                    "notoriety": "Indicateurs de notoriété",
                    "social_media": [
                        {
                            "platform": "Instagram",
                            "url": "https://instagram.com/marque"
                        },
                        {
                            "platform": "TikTok",
                            "url": "https://tiktok.com/@marque"
                        }
                    ]
                }
            ]
        }

        **IMPORTANT : Toutes les valeurs textuelles doivent être rédigées en FRANÇAIS.**

        Maintenant, analyse le lien cible, identifie la catégorie de produit, et fournis les 10 concurrents les plus pertinents dans CETTE catégorie spécifique.
        PROMPT;
    }

    /**
     * Format scraped data into a readable string for the AI prompt
     */
    private function formatScrapedData(array $scrapedData): string
    {
        $parts = [];

        if (!empty($scrapedData['title'])) {
            $parts[] = "- Titre de la page : {$scrapedData['title']}";
        }

        if (!empty($scrapedData['h1'])) {
            $parts[] = "- Titre principal (H1) : {$scrapedData['h1']}";
        }

        if (!empty($scrapedData['meta_description'])) {
            $parts[] = "- Description : {$scrapedData['meta_description']}";
        }

        if (!empty($scrapedData['description'])) {
            $parts[] = "- Description du produit : " . substr($scrapedData['description'], 0, 500);
        }

        if (!empty($scrapedData['price'])) {
            $parts[] = "- Prix : {$scrapedData['price']}";
        }

        if (!empty($scrapedData['category'])) {
            $parts[] = "- Catégorie : {$scrapedData['category']}";
        }

        if (!empty($scrapedData['product_type'])) {
            $parts[] = "- Type de produit : {$scrapedData['product_type']}";
        }

        if (empty($parts)) {
            return "Aucune information n'a pu être extraite de la page. Analyse l'URL pour deviner le type de produit.";
        }

        return implode("\n", $parts);
    }
}
