import { t } from '@core/application';
import React from 'react';
import { Link } from 'react-router-dom';

export default class RememberPasswordLink extends React.Component {
    public render() {
        return <p className="authenticateUser-paragraph isCentered">{t('Remember your password?')} <Link to="/authenticate/password">{t('Sign In')}</Link></p>;
    }
}
