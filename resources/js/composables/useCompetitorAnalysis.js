import { ref } from 'vue';
import axios from 'axios';

/**
 * Composable for competitor analysis logic
 */
export function useCompetitorAnalysis() {
    const competitors = ref([]);
    const isLoading = ref(false);
    const error = ref(null);
    const targetUrl = ref('');

    const analyzeCompetitors = async () => {
        if (!targetUrl.value) {
            error.value = 'Please enter a valid URL';
            return;
        }

        isLoading.value = true;
        error.value = null;
        competitors.value = [];

        try {
            const response = await axios.post('/api/competitors/analyze', {
                target_url: targetUrl.value
            });

            if (response.data.success) {
                competitors.value = response.data.data.competitors;
            } else {
                error.value = response.data.message || 'An error occurred';
            }
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to analyze competitors';
            console.error('Error analyzing competitors:', err);
        } finally {
            isLoading.value = false;
        }
    };

    const resetAnalysis = () => {
        competitors.value = [];
        error.value = null;
        targetUrl.value = '';
    };

    return {
        competitors,
        isLoading,
        error,
        targetUrl,
        analyzeCompetitors,
        resetAnalysis
    };
}
