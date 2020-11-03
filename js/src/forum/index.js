import {extend, override} from 'flarum/extend';
import app from 'flarum/app';
import LogInModal from 'flarum/components/LogInModal';
import SignUpModal from 'flarum/components/SignUpModal';

/* global m */

const translationPrefix = 'clarkwinkelmann-passwordless.forum.sign-up.';

app.initializers.add('clarkwinkelmann-passwordless', () => {
    extend(LogInModal.prototype, 'oninit', function () {
        this.passwordlessTokenSent = false;
        this.passwordlessSkip = !app.forum.attribute('passwordless.passwordlessLoginByDefault');
    });

    extend(LogInModal.prototype, 'fields', function (items) {
        if (this.passwordlessTokenSent) {
            items.add('passwordless', m('p', app.translator.trans(translationPrefix + 'link-sent', {
                a: m('a', {
                    onclick: this.requestPasswordlessToken.bind(this),
                }),
            })), 100);

            if (items.has('identification')) {
                items.remove('identification');
            }

            if (items.has('remember')) {
                items.remove('remember');
            }

            if (items.has('submit')) {
                items.remove('submit');
            }
        } else if (!this.passwordlessSkip) {
            items.add('passwordless', m('p', app.translator.trans(translationPrefix + 'how-it-works')), -100);
        }

        if (!this.passwordlessSkip) {
            if (items.has('password')) {
                items.remove('password');
            }

            if (items.has('identification') && items.get('identification') && Array.isArray(items.get('identification').children)) {
                items.get('identification').children.forEach(vdom => {
                    if (vdom && vdom.attrs && vdom.attrs.name === 'identification') {
                        vdom.attrs.placeholder = app.translator.trans(translationPrefix + 'email');
                        vdom.attrs.type = 'email';
                    }
                });
            }
        }
    });

    extend(LogInModal.prototype, 'footer', function (vdom) {
        // Remove forgot password link
        if (
            !this.passwordlessSkip &&
            Array.isArray(vdom) &&
            vdom.length &&
            vdom[0] &&
            vdom[0].attrs &&
            vdom[0].attrs.className === 'LogInModal-forgotPassword'
        ) {
            vdom.splice(0, 1);
        }

        vdom.push(m('p', app.translator.trans(translationPrefix + (this.passwordlessSkip ? 'login-without-password' : 'login-with-password'), {
            a: m('a', {
                onclick: () => {
                    this.passwordlessSkip = !this.passwordlessSkip;
                    this.passwordlessTokenSent = false;
                },
            }),
        })));
    });

    LogInModal.prototype.requestPasswordlessToken = function (event) {
        event.preventDefault();

        this.loading = true;

        app.request({
            method: 'POST',
            url: app.forum.attribute('apiUrl') + '/passwordless-request',
            body: {
                identification: this.identification(),
                remember: this.remember(),
            },
            errorHandler: this.onerror.bind(this),
        }).then(() => {
            this.passwordlessTokenSent = true;
            this.loaded();
        }, this.loaded.bind(this));
    };

    override(LogInModal.prototype, 'onsubmit', function (original, event) {
        if (this.passwordlessSkip) {
            return original(event);
        }

        this.requestPasswordlessToken(event);
    });

    extend(SignUpModal.prototype, 'fields', function (items) {
        if (app.forum.attribute('passwordless.hideSignUpPassword') && items.has('password')) {
            items.remove('password');
        }
    });
});
