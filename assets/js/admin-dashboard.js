document.addEventListener("DOMContentLoaded", () => {
  const { createApp } = Vue;

  createApp({
    data() {
      return {
        stats: [],
        labels: [],
        counts: [],
        chart: null,
        errorMsg: '',
        loading: false,
        filters: {
          period: '',
          source: '',
          device: ''
        }
      };
    },
    mounted() {
      this.fetchStats();
    },
    methods: {
      async fetchStats() {
        this.errorMsg = '';
        this.loading = true;

        const params = new URLSearchParams({
          action: 'bba_get_stats',
          nonce: bba_ajax.nonce,
          ...this.filters
        });

        try {
          const res = await fetch(`${bba_ajax.url}?${params.toString()}`);
          const text = await res.text();

          try {
            const data = JSON.parse(text);
            this.stats = data;
            this.labels = data.map(d => d.date);
            this.counts = data.map(d => parseInt(d.views));
            this.renderChart();
          } catch (jsonErr) {
            console.error("Réponse non-JSON reçue :", text);
            throw new Error('Erreur JSON : ' + jsonErr.message);
          }
        } catch (err) {
          console.error('Erreur fetchStats:', err);
          this.errorMsg = "❌ Impossible de charger les statistiques.";
        } finally {
          this.loading = false;
        }
      },

      renderChart() {
        const canvas = document.getElementById("visitsChart");
        if (!canvas) return;
        const ctx = canvas.getContext("2d");

        if (this.chart) this.chart.destroy();

        this.chart = new Chart(ctx, {
          type: "line",
          data: {
            labels: this.labels,
            datasets: [{
              label: "Pages vues",
              data: this.counts,
              borderColor: "#4F46E5",
              backgroundColor: "rgba(79, 70, 229, 0.2)",
              fill: true,
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
            scales: {
              x: { title: { display: true, text: "Date" } },
              y: { title: { display: true, text: "Visites" }, beginAtZero: true }
            },
            plugins: {
              legend: { position: "top" }
            }
          }
        });
      }
    }
  }).mount("#bba-dashboard");
});
