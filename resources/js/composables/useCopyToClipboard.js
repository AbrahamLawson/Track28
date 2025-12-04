import { ref } from 'vue';

export function useCopyToClipboard() {
    const copied = ref(false);
    const copiedId = ref(null);

    const copyToClipboard = async (text, id = null) => {
        try {
            await navigator.clipboard.writeText(text);
            copied.value = true;
            copiedId.value = id;

            // Reset after 2 seconds
            setTimeout(() => {
                copied.value = false;
                copiedId.value = null;
            }, 2000);

            return true;
        } catch (error) {
            console.error('Failed to copy to clipboard:', error);
            return false;
        }
    };

    const formatCompetitorForCopy = (competitor) => {
        let text = `${competitor.name}\n`;
        text += `${'='.repeat(competitor.name.length)}\n\n`;

        if (competitor.positioning) {
            text += `Positionnement: ${competitor.positioning}\n`;
        }

        if (competitor.price) {
            text += `Prix: $${parseFloat(competitor.price).toFixed(2)}\n`;
        }

        if (competitor.product_url) {
            text += `URL: ${competitor.product_url}\n`;
        }

        if (competitor.description) {
            text += `\nDescription:\n${competitor.description}\n`;
        }

        if (competitor.strengths) {
            text += `\n💪 Points forts:\n${competitor.strengths}\n`;
        }

        if (competitor.notoriety) {
            text += `\n⭐ Notoriété:\n${competitor.notoriety}\n`;
        }

        if (competitor.social_media && competitor.social_media.length > 0) {
            text += `\n📱 Réseaux sociaux:\n`;
            competitor.social_media.forEach(social => {
                const emoji = getSocialMediaEmoji(social.platform);
                const followers = social.followers ? ` - ${formatFollowers(social.followers)} abonnés` : '';
                text += `${emoji} ${social.platform}: ${social.url}${followers}\n`;
            });
        }

        return text;
    };

    const formatFollowers = (count) => {
        if (count >= 1000000) {
            return `${(count / 1000000).toFixed(1)}M`;
        } else if (count >= 1000) {
            return `${(count / 1000).toFixed(1)}K`;
        }
        return count.toString();
    };

    const getSocialMediaEmoji = (platform) => {
        const emojis = {
            'Instagram': '📸',
            'Facebook': '👥',
            'TikTok': '🎵',
            'Twitter': '🐦',
            'LinkedIn': '💼',
            'YouTube': '🎥'
        };
        return emojis[platform] || '🔗';
    };

    const formatAllCompetitorsForCopy = (competitors) => {
        let text = `Analyse des concurrents - ${competitors.length} résultats\n`;
        text += `${'='.repeat(50)}\n\n`;

        competitors.forEach((competitor, index) => {
            text += `${index + 1}. ${formatCompetitorForCopy(competitor)}\n`;
            text += `${'-'.repeat(50)}\n\n`;
        });

        return text;
    };

    return {
        copied,
        copiedId,
        copyToClipboard,
        formatCompetitorForCopy,
        formatAllCompetitorsForCopy
    };
}
