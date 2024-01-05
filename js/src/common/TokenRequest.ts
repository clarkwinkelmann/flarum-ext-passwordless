import app from 'flarum/common/app';
import Component, {ComponentAttrs} from 'flarum/common/Component';
import RequestError from 'flarum/common/utils/RequestError';
import LoadingIndicator from 'flarum/common/components/LoadingIndicator';

interface TokenRequestAttrs extends ComponentAttrs {
    errorHandler?: (error: RequestError) => void
    onErrorClear?: () => void
    additionalText?: string
}

export default class TokenRequest extends Component<TokenRequestAttrs> {
    sending: boolean = false
    sent: boolean = false

    view() {
        return m('.PasswordlessTokenRequest', [
            m('.PasswordlessTokenRequest-send', [
                app.translator.trans('clarkwinkelmann-passwordless.lib.token-request.' + (this.sent ? 'sent' : 'send'), {
                    a: m('a', {
                        onclick: this.requestPasswordlessToken.bind(this),
                        disabled: this.sending,
                    }),
                }),
                this.sending ? [' ', LoadingIndicator.component({
                    display: 'inline',
                    size: 'small',
                })] : null,
            ]),
            this.attrs.additionalText ? m('.PasswordlessTokenRequest-more', this.attrs.additionalText) : null,
        ]);
    }

    requestPasswordlessToken(event: Event) {
        event.preventDefault();

        this.sending = true;

        app.request({
            method: 'POST',
            url: app.forum.attribute('apiUrl') + '/passwordless-request',
            body: {
                oneTimeToken: true,
            },
            errorHandler: this.attrs.errorHandler,
        }).then(() => {
            this.sending = false;
            this.sent = true;
            if (this.attrs.onErrorClear) {
                this.attrs.onErrorClear();
            }
            m.redraw();
        }).catch(error => {
            this.sending = false;
            m.redraw();
            throw error;
        });
    }
}
