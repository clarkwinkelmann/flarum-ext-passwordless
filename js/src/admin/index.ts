import app from 'flarum/admin/app';
import Switch from 'flarum/common/components/Switch';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import TokenRequest from '../common/TokenRequest';

export {TokenRequest};

const settingsPrefix = 'passwordless.';
const translationPrefix = 'clarkwinkelmann-passwordless.admin.settings.';

app.initializers.add('clarkwinkelmann-passwordless', () => {
    app.extensionData
        .for('clarkwinkelmann-passwordless')
        .registerSetting(function (this: ExtensionPage) {
            return [
                m('.Form-group', [
                    Switch.component({
                        state: this.setting(settingsPrefix + 'passwordlessLoginByDefault', '1')() === '1',
                        onchange: (value: boolean) => {
                            this.setting(settingsPrefix + 'passwordlessLoginByDefault')(value ? '1' : '0');
                        },
                    }, app.translator.trans(translationPrefix + 'passwordless-login-by-default')),
                ]),
                m('.Form-group', [
                    Switch.component({
                        state: this.setting(settingsPrefix + 'hideSignUpPassword', '1')() === '1',
                        onchange: (value: boolean) => {
                            this.setting(settingsPrefix + 'hideSignUpPassword')(value ? '1' : '0');
                        },
                    }, app.translator.trans(translationPrefix + 'hide-sign-up-password')),
                ]),
                m('.Form-group', [
                    m('label', app.translator.trans(translationPrefix + 'token-life')),
                    m('input.FormControl', {
                        type: 'number',
                        bidi: this.setting(settingsPrefix + 'tokenLifeInMinutes', 5),
                    }),
                ]),
            ];
        });
});
