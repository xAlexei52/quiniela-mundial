# ⚽ Quiniela Mageova · Mundial 2026

App en **Laravel 12** para correr una quiniela del Mundial 2026 entre **12 participantes**:

- **12 participantes × 4 equipos = 48.** Los equipos se asignan **a mano** desde el panel admin.
- **Sin login:** la web es pública (solo lectura). El panel `/admin` se protege con un **PIN**.
- **Sistema por puntos** (todo el torneo): victoria **3**, empate **1**, derrota **0**, más un
  **bonus por la ronda más lejana** que alcanza cada equipo:
  Dieciseisavos **+5**, Octavos **+8**, Cuartos **+12**, Semifinal **+15**, 3er lugar **+18**,
  Subcampeón **+20**, Campeón **+25**.
- **Premios:** bote configurable (por defecto **$6,000**) repartido al **top 3** (50% / 30% / 20%).
- **Resultados desde la API** (football-data.org); también hay carga manual de respaldo.
- **Portada** con marcador, gráfica de puntos, premios y resultados.
- **Diseño oscuro** estilo marcador deportivo (banderas reales vía flagcdn/flagpedia).
- **Deploy automático a Hostinger** por FTP con GitHub Actions.

---

## 🎮 Cómo se juega

1. El **admin** entra a `/admin` con el PIN, registra a los 12 participantes y asigna **4 equipos** a
   cada uno (o usa el botón **Repartir al azar**).
2. Durante el Mundial los marcadores entran **desde la API** (botón *Sincronizar* o por cron).
3. La app calcula posiciones de grupo, clasificados, eliminatorias y el **marcador** de la quiniela.
4. Gana quien sume más puntos (3/1/0 por partido + bonus por ronda). Al final se reparte el bote
   entre el **top 3**.

**Acceso admin:** `/admin` → PIN definido en `APP_ADMIN_PIN` (por defecto `2026`, **cámbialo**).

---

## 💻 Desarrollo local

Requisitos: PHP 8.2+, Composer, Node 18+.

```bash
composer install
npm install
cp .env.example .env        # en Windows: copy .env.example .env
php artisan key:generate

# Local rápido con SQLite: pon DB_CONNECTION=sqlite en .env y crea el archivo:
#   touch database/database.sqlite   (o New-Item database/database.sqlite)

php artisan migrate --seed              # crea tablas + 12 participantes de plantilla
php artisan quiniela:import-fixture     # carga equipos/grupos/partidos (API si hay key, o respaldo)
npm run build                            # o: npm run dev
php artisan serve
```

Entra en http://localhost:8000 (web pública). Para administrar: `/admin` con el PIN.

---

## 🚀 Despliegue en Hostinger (FTP + GitHub Actions)

### 0. PHP 8.2+
hPanel → **Avanzado → Configuración PHP** → selecciona **PHP 8.2 o superior**.

### 1. Base de datos MySQL
hPanel → **Bases de datos MySQL** → crea BD y usuario. Anota host, BD, usuario y contraseña.

### 2. `.env` del servidor
Crea el `.env` (copiando `.env.example`) y llena:
- `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `APP_URL=https://tudominio.com`
- `APP_KEY=` → genera con `php artisan key:generate --show` y pégalo.
- `APP_ADMIN_PIN=` → tu PIN de administración.
- (Opcional) `RESULTS_API_KEY` para sincronizar resultados desde la API.
- (Opcional) `QUINIELA_PRIZE_POOL` y `QUINIELA_CURRENCY` para el bote.

> El `.env` **no** se sube por el pipeline (está excluido).

### 3. Secrets en GitHub
**Settings → Secrets and variables → Actions**:

| Secret           | Valor                                                            |
|------------------|------------------------------------------------------------------|
| `FTP_SERVER`     | Host FTP de Hostinger                                            |
| `FTP_USERNAME`   | Usuario FTP                                                      |
| `FTP_PASSWORD`   | Contraseña FTP                                                   |
| `FTP_SERVER_DIR` | Carpeta destino, terminada en `/`                               |

### 4. Document root → `public`
Apunta el dominio a la carpeta `public/` del proyecto (hPanel → Dominios → Document root).

### 5. Despliega
`git push` a **main** → el workflow instala dependencias, compila assets y sube por FTP.

### 6. Migraciones + carga inicial
- **Con SSH:** `php artisan migrate --force && php artisan db:seed --force && php artisan quiniela:import-fixture`
- **Sin SSH:** pon `APP_SETUP_TOKEN=algo-secreto` en el `.env` y visita **una vez**
  `https://tudominio.com/setup/algo-secreto`. Luego **borra el token**.

### 7. (Opcional) Sincronización automática
Con `RESULTS_API_KEY`, programa un **cron** en hPanel → **Cron Jobs**:

```bash
# cada 10 minutos durante el torneo:
php /ruta/al/proyecto/artisan quiniela:sync-results
```

---

## 🔌 API de resultados

Driver por defecto: [football-data.org](https://www.football-data.org/) (plan gratuito).
1. Regístrate y copia tu token → `RESULTS_API_KEY` (competición `2000` = Mundial).
2. `php artisan quiniela:import-fixture` y `php artisan quiniela:sync-results`.

Sin API key, todo funciona; los marcadores entran desde la API cuando la configures.

---

## 📝 Notas

- Las banderas se sirven desde **flagcdn.com** (flagpedia): el código ISO-2 se deriva del emoji del
  equipo; Inglaterra/Escocia/Gales usan `gb-eng/gb-sct/gb-wls`. Si algo falla, se usa el emoji.
- El puntaje y el bono por ronda están en `app/Services/ScoringService.php`.
- El bote y su reparto se configuran en `config/quiniela.php` (`prize`).
- El cuadro de eliminatorias usa un sembrado simplificado y se recalcula con cada sincronización.
