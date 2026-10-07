import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

import { Application } from '@hotwired/stimulus';
import WebsiteChatController from './controllers/website_chat_controller';
const stimulus = Application.start();
stimulus.register('website-chat', WebsiteChatController);
