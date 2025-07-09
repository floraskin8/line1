
<div id="bba-dashboard" class="wrap" style="max-width: 900px; margin-top: 2rem;">
  <h1>📈 BlackBeast Analytics</h1>


  <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; margin-top: 1.5rem;">
    
    <label style="display: flex; flex-direction: column;">
      <strong>Période :</strong>
      <select v-model="filters.period" style="min-width: 160px;">
        <option value="">-- Toutes --</option>
        <option value="today">Aujourd’hui</option>
        <option value="7days">7 derniers jours</option>
        <option value="30days">30 derniers jours</option>
      </select>
    </label>

    <label style="display: flex; flex-direction: column;">
      <strong>Source :</strong>
      <select v-model="filters.source" style="min-width: 160px;">
        <option value="">-- Toutes --</option>
        <option value="direct">Direct</option>
        <option value="search">Moteur de recherche</option>
        <option value="social">Réseaux sociaux</option>
        <option value="referral">Lien externe</option>
      </select>
    </label>

    <label style="display: flex; flex-direction: column;">
      <strong>Appareil :</strong>
      <select v-model="filters.device" style="min-width: 140px;">
        <option value="">-- Tous --</option>
        <option value="desktop">Desktop</option>
        <option value="mobile">Mobile</option>
      </select>
    </label>

    <button @click="fetchStats"
            :disabled="loading"
            style="padding: 0.4rem 1rem; background-color: #4F46E5; color: white; border: none; border-radius: 4px; cursor: pointer;">
      {{ loading ? 'Chargement...' : '🔍 Appliquer' }}
    </button>
  </div>

  <!-- ⚠️ Message d'erreur -->
  <p v-if="errorMsg" style="color: red; margin-top: 1rem;">{{ errorMsg }}</p>

  <!-- 📊 Graphique -->
  <canvas id="visitsChart" style="margin-top: 2rem; width: 100%; height: 400px;"></canvas>
</div>
