import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import L from 'leaflet';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

L.Icon.Default.mergeOptions({
    iconUrl: markerIcon,
    iconRetinaUrl: markerIcon2x,
    shadowUrl: markerShadow,
});

const escapeHtml = (value) =>
    String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

/**
 * Carte OpenStreetMap.
 * <div x-data="pratikMap({ markers: [{lat, lng, title, url, image, subtitle}], center: [5.35, -4.0] })"></div>
 */
Alpine.data('pratikMap', ({ markers = [], center = [5.3484, -4.0305], zoom = 12, user = null } = {}) => ({
    map: null,
    init() {
        this.$nextTick(() => {
            this.map = L.map(this.$el, { scrollWheelZoom: false }).setView(center, zoom);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap',
            }).addTo(this.map);

            const points = [];
            markers
                .filter((m) => Number(m.lat) && Number(m.lng))
                .forEach((m) => {
                    const html = `
                        <div style="min-width:180px">
                            ${m.image ? `<img src="${escapeHtml(m.image)}" alt="" style="width:100%;height:90px;object-fit:cover;border-radius:6px;margin-bottom:6px">` : ''}
                            <strong style="display:block;font-size:13px">${escapeHtml(m.title)}</strong>
                            ${m.subtitle ? `<span style="font-size:12px;color:#6b7280">${escapeHtml(m.subtitle)}</span>` : ''}
                            ${m.url ? `<a href="${escapeHtml(m.url)}" style="display:block;margin-top:6px;font-size:12px;font-weight:600">Voir la fiche →</a>` : ''}
                        </div>`;
                    L.marker([Number(m.lat), Number(m.lng)]).addTo(this.map).bindPopup(html);
                    points.push([Number(m.lat), Number(m.lng)]);
                });

            if (user) {
                L.circleMarker(user, { radius: 8, color: '#17724a', fillColor: '#17724a', fillOpacity: 0.9 })
                    .addTo(this.map)
                    .bindPopup('Vous êtes ici');
                points.push(user);
            }

            if (points.length > 1) {
                this.map.fitBounds(points, { padding: [30, 30], maxZoom: 15 });
            } else if (points.length === 1) {
                this.map.setView(points[0], 15);
            }
        });
    },
}));

/** Géolocalisation pour "pharmacies autour de moi". */
Alpine.data('geoForm', () => ({
    loading: false,
    error: '',
    locate() {
        if (!navigator.geolocation) {
            this.error = "Votre navigateur ne permet pas la géolocalisation.";
            return;
        }
        this.loading = true;
        this.error = '';
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.$refs.lat.value = pos.coords.latitude;
                this.$refs.lng.value = pos.coords.longitude;
                this.$root.submit();
            },
            () => {
                this.loading = false;
                this.error = "Impossible d'obtenir votre position. Autorisez la localisation puis réessayez.";
            },
            { enableHighAccuracy: true, timeout: 10000 },
        );
    },
}));

/** Galerie photo avec visionneuse (fiche bien / fiche entreprise). */
Alpine.data('gallery', (images = []) => ({
    images,
    current: 0,
    open: false,
    show(i) {
        this.current = i;
        this.open = true;
    },
    next() {
        this.current = (this.current + 1) % this.images.length;
    },
    prev() {
        this.current = (this.current - 1 + this.images.length) % this.images.length;
    },
}));

/** Compte un clic "appeler" / "écrire" pour les statistiques de l'annonceur. */
window.pratikTrack = (url) => {
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const body = new FormData();
        body.append('_token', token);
        navigator.sendBeacon ? navigator.sendBeacon(url, body) : fetch(url, { method: 'POST', body });
    } catch (e) {
        /* sans importance */
    }
};

/* ------------------------------------------------------------------
 * Module Immobilier — espace annonceur
 * ------------------------------------------------------------------ */

/**
 * Carte cliquable pour positionner un bien : un clic (ou le déplacement du repère)
 * remplit les champs latitude / longitude référencés par x-ref="lat" / x-ref="lng".
 * <div x-data="mapPicker({ lat: 5.35, lng: -4.0 })"> <div x-ref="carte"></div> <input x-ref="lat"> <input x-ref="lng"> </div>
 */
Alpine.data('mapPicker', ({ lat = null, lng = null } = {}) => ({
    map: null,
    marker: null,
    locating: false,
    init() {
        this.$nextTick(() => {
            const start = lat && lng ? [Number(lat), Number(lng)] : [5.3484, -4.0305];
            this.map = L.map(this.$refs.carte, { scrollWheelZoom: false }).setView(start, lat && lng ? 16 : 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(this.map);
            if (lat && lng) this.place(start, false);
            this.map.on('click', (e) => this.place([e.latlng.lat, e.latlng.lng]));
        });
    },
    place(point, recentrer = true) {
        if (!this.marker) {
            this.marker = L.marker(point, { draggable: true }).addTo(this.map);
            this.marker.on('dragend', () => {
                const p = this.marker.getLatLng();
                this.write(p.lat, p.lng);
            });
        } else {
            this.marker.setLatLng(point);
        }
        if (recentrer) this.map.panTo(point);
        this.write(point[0], point[1]);
    },
    write(a, b) {
        this.$refs.lat.value = Number(a).toFixed(6);
        this.$refs.lng.value = Number(b).toFixed(6);
    },
    effacer() {
        if (this.marker) this.map.removeLayer(this.marker);
        this.marker = null;
        this.$refs.lat.value = '';
        this.$refs.lng.value = '';
    },
    maPosition() {
        if (!navigator.geolocation) return;
        this.locating = true;
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.locating = false;
                this.place([pos.coords.latitude, pos.coords.longitude]);
                this.map.setZoom(17);
            },
            () => (this.locating = false),
            { enableHighAccuracy: true, timeout: 10000 },
        );
    },
}));

/** Aperçu des photos choisies avant l'envoi du formulaire. */
Alpine.data('photosPreview', () => ({
    apercus: [],
    choisir(event) {
        this.apercus.forEach((a) => URL.revokeObjectURL(a.url));
        this.apercus = Array.from(event.target.files || [])
            .filter((f) => f.type.startsWith('image/'))
            .map((f) => ({ url: URL.createObjectURL(f), nom: f.name, taille: (f.size / 1048576).toFixed(1) }));
    },
}));

Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();
