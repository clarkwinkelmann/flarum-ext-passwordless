import {Vnode} from 'mithril';
import {extend, override} from 'flarum/common/extend';
import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import ItemList from 'flarum/common/utils/ItemList';
import LogInModal from 'flarum/forum/components/LogInModal';
import SignUpModal from 'flarum/forum/components/SignUpModal';

const translationPrefix = 'clarkwinkelmann-passwordless.forum.sign-up.';

app.initializers.add('clarkwinkelmann-passwordless', () => {
    extend(LogInModal.prototype, 'oninit', function () {
        this.passwordlessTokenSent = false;
        this.passwordlessSkip = !app.forum.attribute('passwordless.passwordlessLoginByDefault');
    });

    extend(LogInModal.prototype, 'fields', function (items: ItemList<any>) {
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
                items.get('identification').children.forEach((vdom: any) => {
                    if (vdom && vdom.attrs && vdom.attrs.name === 'identification') {
                        vdom.attrs.placeholder = app.translator.trans(translationPrefix + 'email');
                        vdom.attrs.type = 'email';
                    }
                });
            }
        }
    });

    // Flarum 1.7 and lower returned an array of nodes. Flarum 1.8 and above returns an anonymous node
    extend(LogInModal.prototype, 'footer', function (vdom: Vnode | Vnode[]) {
        if (!vdom) {
            return;
        }

        const children = Array.isArray(vdom) ? vdom : vdom.children;

        // Graceful fail if we can't find the children list
        if (!Array.isArray(children)) {
            console.warn('[passwordless] skipped LogInModal.footer');
            return;
        }

        // Remove forgot password link
        if (
            !this.passwordlessSkip &&
            children.length &&
            children[0] &&
            children[0].attrs &&
            children[0].attrs.className === 'LogInModal-forgotPassword'
        ) {
            children.splice(0, 1);
        }

        children.push(m('p', app.translator.trans(translationPrefix + (this.passwordlessSkip ? 'login-without-password' : 'login-with-password'), {
            a: m('a', {
                onclick: () => {
                    this.passwordlessSkip = !this.passwordlessSkip;
                    this.passwordlessTokenSent = false;
                },
            }),
        })));
    });

    LogInModal.prototype.requestPasswordlessToken = function (this: LogInModal, event: Event) {
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

    override(LogInModal.prototype, 'onerror', function (original, error) {
        // Unless you have some other extensions throwing 401 errors, it's very likely that this means the account doesn't exist yet
        // Therefore we will offer a quick access to the signup form from the error message
        if (error.status === 401 && error.alert && !this.passwordlessSkip && app.forum.attribute('allowSignUp')) {
            error.alert.controls = error.alert.controls || [];
            error.alert.controls.unshift(app.translator.trans(translationPrefix + 'error-try-signup', {
                a: Button.component({
                    className: 'Button Button--link',
                    onclick: this.signUp.bind(this),
                }),
            }));
        }

        return original(error);
    });

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
