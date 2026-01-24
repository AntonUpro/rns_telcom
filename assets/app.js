import { registerVueControllerComponents } from '@symfony/ux-vue';
import { startStimulusApp } from '@symfony/stimulus-bridge';
import {VueElementConstructor} from "vue";

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './styles/calculation.css';

// Запуск Stimulus-приложения и автоподключение контроллеров из assets/controllers/*
const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

registerVueControllerComponents(require.context('./vue/controllers', true, /\.vue$/));
