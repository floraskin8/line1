document.addEventListener("DOMContentLoaded", () => {
  const { createApp } = Vue;

  createApp({
    data() {
      return {
        stats: [],
        labels: [],
        counts: [],
        chart: null,
        filters: {
          period: '',
          source: '',
          device: ''
        },
        loading: false,
        errorMsg: ''
      };
    },
    mounted() {
      this.fetchStats();
    },
    methods: {
      async fetchStats() {
  this.loading = true;
  this.errorMsg = '';

  const params = new URLSearchParams({
    action: 'bba_get_stats',
    nonce: bba_ajax.nonce,
    ...this.filters
  });

  try {
    const res = await fetch(bba_ajax.url + "?" + params.toString());
    if (!res.ok) throw new Error(`Erreur réseau: ${res.status}`);

    const data = await res.json();

    if (!Array.isArray(data)) {
      throw new Error('Réponse invalide : attendu un tableau');
    }

    this.stats = data;
    this.labels = data.map(d => d.date);
    this.counts = data.map(d => parseInt(d.views));

    if (this.counts.length === 0) {
      this.destroyChart();
      this.errorMsg = 'Aucune donnée à afficher.';
    } else {
      this.renderChart();
    }
  } catch (error) {
    this.errorMsg = error.message || 'Erreur lors de la récupération des données.';
    console.error('Erreur fetchStats:', error);
    this.destroyChart();
  } finally {
    this.loading = false;
  }
},

      renderChart() {
        this.destroyChart();

        const canvas = document.getElementById("visitsChart");
        if (!canvas) {
          console.error('Canvas #visitsChart introuvable');
          return;
        }
        const ctx = canvas.getContext("2d");
        if (!ctx) {
          console.error('Impossible d\'obtenir le contexte 2D du canvas');
          return;
        }

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
              tension: 0.3
            }]
          },
          options: {
            responsive: true,
            scales: {
              x: { title: { display: true, text: "Date" } },
              y: { title: { display: true, text: "Visites" }, beginAtZero: true }
            },
            plugins: {
              legend: { display: true, position: "top" }
            }
          }
        });
      },
      destroyChart() {
        if (this.chart) {
          this.chart.destroy();
          this.chart = null;
        }
      }
    }
  }).mount("#bba-dashboard");
});
