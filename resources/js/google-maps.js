import Places from './components/Places.vue';

Statamic.booting(() => {
    Statamic.$components.register('places-fieldtype', Places);
});
