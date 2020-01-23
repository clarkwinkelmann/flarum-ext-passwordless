import {extend} from 'flarum/extend';
import app from 'flarum/app';
import PasswordlessSettingsModal from './components/PasswordlessSettingsModal';

app.initializers.add('clarkwinkelmann-passwordless', () => {
    app.extensionSettings['clarkwinkelmann-passwordless'] = () => app.modal.show(new PasswordlessSettingsModal());
});
