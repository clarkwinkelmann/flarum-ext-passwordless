import app from 'flarum/app';
import SettingsModal from 'flarum/components/SettingsModal';
import Switch from 'flarum/components/Switch';

/* global m */

const settingsPrefix = 'passwordless.';
const translationPrefix = 'clarkwinkelmann-passwordless.admin.settings.';

export default class PasswordlessSettingsModal extends SettingsModal {
    title() {
        return app.translator.trans(translationPrefix + 'title');
    }

    form() {
        return [
            m('.Form-group', [
                Switch.component({
                    state: this.setting(settingsPrefix + 'passwordlessLoginByDefault', '1')() === '1',
                    onchange: value => {
                        this.setting(settingsPrefix + 'passwordlessLoginByDefault')(value ? '1' : '0');
                    },
                    children: app.translator.trans(translationPrefix + 'passwordless-login-by-default'),
                }),
            ]),
            m('.Form-group', [
                Switch.component({
                    state: this.setting(settingsPrefix + 'hideSignUpPassword', '1')() === '1',
                    onchange: value => {
                        this.setting(settingsPrefix + 'hideSignUpPassword')(value ? '1' : '0');
                    },
                    children: app.translator.trans(translationPrefix + 'hide-sign-up-password'),
                }),
            ]),
            m('.Form-group', [
                m('label', app.translator.trans(translationPrefix + 'token-life')),
                m('input.FormControl', {
                    type: 'number',
                    bidi: this.setting(settingsPrefix + 'tokenLifeInMinutes', 5),
                }),
            ]),
        ];
    }
}
