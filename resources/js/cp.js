import Places from './fieldtypes/Places.vue';

Statamic.booting(() => {
    Statamic.$components.register('places-fieldtype', Places);
});
