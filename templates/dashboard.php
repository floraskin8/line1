<div id="bba-dashboard" class="wrap">
  <h1>📈 BlackBeast Analytics</h1>

  <div>
    <label>Période :
      <select v-model="filters.period">
        <option value="">-- Toutes --</option>
        <option value="today">Aujourd’hui</option>
        <option value="7days">7 derniers jours</option>
        <option value="30days">30 derniers jours</option>
      </select>
    </label>

    <label>Source :
      <select v-model="filters.source">
        <option value="">-- Toutes --</option>
        <option value="direct">Direct</option>
        <option value="search">Moteur de recherche</option>
        <option value="social">Réseaux sociaux</option>
        <option value="referral">Lien externe</option>
      </select>
    </label>

    <label>Appareil :
      <select v-model="filters.device">
        <option value="">-- Tous --</option>
        <option value="desktop">Desktop</option>
        <option value="mobile">Mobile</option>
      </select>
    </label>

    <button @click="fetchStats" :disabled="loading">
      {{ loading ? 'Chargement...' : '🔍 Appliquer' }}
    </button>
  </div>

  <p v-if="errorMsg" style="color:red; margin-top:1rem;">{{ errorMsg }}</p>

  <canvas id="visitsChart" style="margin-top: 2rem; max-width: 100%; height: 400px;"></canvas>
</div>
