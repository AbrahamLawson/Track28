<template>
    <div v-if="competitors.length > 0" class="space-y-3 sm:space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3">
            <h2 class="text-lg sm:text-xl md:text-2xl font-semibold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">
                {{ competitors.length }} concurrent{{ competitors.length !== 1 ? 's' : '' }} trouvé{{ competitors.length !== 1 ? 's' : '' }}
            </h2>
            <button
                @click="copyAllCompetitors"
                class="flex items-center gap-2 px-3 sm:px-4 py-2 text-sm sm:text-base bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium cursor-pointer w-full sm:w-auto justify-center"
            >
                <svg
                    v-if="!copied || copiedId !== 'all'"
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                </svg>
                <svg
                    v-else
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
                {{ copied && copiedId === 'all' ? 'Copié !' : 'Copier tout' }}
            </button>
        </div>

        <div
            v-for="(competitor, index) in competitors"
            :key="index"
            class="rounded-xl border border-gray-300 shadow-lg p-4 sm:p-6 transition-all hover:shadow-xl"
            style="background-color: #FAF7F2;"
        >
            <div class="flex flex-col sm:flex-row justify-between items-start mb-4 gap-3">
                <div class="flex-1 w-full sm:w-auto">
                    <h3 class="text-lg sm:text-xl font-semibold mb-2 text-gray-900">
                        {{ competitor.name }}
                    </h3>
                    <span
                        v-if="competitor.positioning"
                        class="inline-block px-2 sm:px-3 py-1 text-xs sm:text-sm font-medium rounded-full bg-blue-50 text-blue-700 border border-blue-200"
                    >
                        {{ competitor.positioning }}
                    </span>
                </div>
                <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto justify-between sm:justify-end">
                    <span
                        v-if="competitor.price"
                        class="text-base sm:text-lg font-bold text-gray-900"
                    >
                        ${{ formatPrice(competitor.price) }}
                    </span>
                    <button
                        @click="copyCompetitor(competitor, index)"
                        class="flex items-center gap-1 px-2 sm:px-3 py-1.5 text-xs sm:text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
                        :class="{ 'bg-green-50 border-green-300 text-green-700': copied && copiedId === index }"
                    >
                        <svg
                            v-if="!copied || copiedId !== index"
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        {{ copied && copiedId === index ? 'Copié' : 'Copier' }}
                    </button>
                </div>
            </div>

            <p
                v-if="competitor.description"
                class="text-sm sm:text-base text-gray-600 mb-3 sm:mb-4"
            >
                {{ competitor.description }}
            </p>

            <div
                v-if="competitor.strengths"
                class="mb-3 p-3 sm:p-4 rounded-lg bg-green-50 border border-green-200"
            >
                <p class="text-xs sm:text-sm font-semibold text-green-800 mb-1">💪 Points forts</p>
                <p class="text-xs sm:text-sm text-green-700">{{ competitor.strengths }}</p>
            </div>

            <div
                v-if="competitor.notoriety"
                class="mb-3 sm:mb-4 p-3 sm:p-4 rounded-lg bg-purple-50 border border-purple-200"
            >
                <p class="text-xs sm:text-sm font-semibold text-purple-800 mb-1">⭐ Notoriété</p>
                <p class="text-xs sm:text-sm text-purple-700">{{ competitor.notoriety }}</p>
            </div>

            <div
                v-if="competitor.social_media && competitor.social_media.length > 0"
                class="mb-3 sm:mb-4 p-3 sm:p-4 rounded-lg bg-indigo-50 border border-indigo-200"
            >
                <p class="text-xs sm:text-sm font-semibold text-indigo-800 mb-2 sm:mb-3">📱 Réseaux sociaux</p>
                <div class="grid grid-cols-1 xs:grid-cols-2 sm:flex sm:flex-wrap gap-2">
                    <a
                        v-for="(social, socialIndex) in competitor.social_media"
                        :key="socialIndex"
                        :href="social.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-2 sm:px-3 py-2 bg-white rounded-lg border border-indigo-300 hover:bg-indigo-100 transition-colors text-xs sm:text-sm"
                    >
                        <span class="text-base sm:text-lg">{{ getSocialMediaEmoji(social.platform) }}</span>
                        <div class="flex flex-col flex-1">
                            <span class="font-medium text-indigo-900 text-xs sm:text-sm">{{ social.platform }}</span>
                            <span v-if="social.followers" class="text-xs text-indigo-600">
                                {{ formatFollowers(social.followers) }} abonnés
                            </span>
                        </div>
                        <svg
                            class="w-3 h-3 text-indigo-600"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                            />
                        </svg>
                    </a>
                </div>
            </div>

            <a
                v-if="competitor.product_url"
                :href="competitor.product_url"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center text-sm sm:text-base text-blue-600 hover:text-blue-700 font-medium transition-colors"
            >
                Visiter le site
                <svg
                    class="w-4 h-4 ml-1"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                    />
                </svg>
            </a>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';
import { useCopyToClipboard } from '../composables/useCopyToClipboard';

const props = defineProps({
    competitors: {
        type: Array,
        required: true,
        default: () => []
    }
});

const {
    copied,
    copiedId,
    copyToClipboard,
    formatCompetitorForCopy,
    formatAllCompetitorsForCopy
} = useCopyToClipboard();

const formatPrice = (price) => {
    return parseFloat(price).toFixed(2);
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

const copyCompetitor = (competitor, index) => {
    const text = formatCompetitorForCopy(competitor);
    copyToClipboard(text, index);
};

const copyAllCompetitors = () => {
    const text = formatAllCompetitorsForCopy(props.competitors);
    copyToClipboard(text, 'all');
};
</script>
