var map = L.map('map', {maxBounds: new L.LatLngBounds([40.57, 15.13], [49.26, 34.63]) });
map.setView([45.92059, 24.98291], 6);

L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {
        minZoom: 6,
        maxZoom: 15,
        noWrap: true,
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
        '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
        'Imagery © <a href="http://mapbox.com">Mapbox</a>',
        id: 'examples.map-i875mjb7'
}).addTo(map);


var popup = L.popup();

function onMapClick(e) {
        popup
        .setLatLng(e.latlng)
        .setContent("You clicked the map at " + e.latlng.toString())
        .openOn(map);
}

map.on('click', onMapClick);