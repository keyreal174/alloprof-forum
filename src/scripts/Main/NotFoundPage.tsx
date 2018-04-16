import React from 'react';
import { sprintf } from 'sprintf-js';
import { t } from '@core/application';
import DocumentTitle from '@core/Components/DocumentTitle';

export default class NotFoundPage extends React.PureComponent<IProps> {
    public static defaultProps = {
        type: "Page",
    };

    public render() {
        return <div className="Center SplashInfo">
            <DocumentTitle title={this.title}/>
            <div>{this.message}</div>
        </div>;
    }

    private get title() {
        return this.props.title || sprintf(t('%s Not Found'), t(this.props.type));
    }

    private get message() {
        return this.props.message || sprintf(t('The %s you were looking for could not be found.'), t(this.props.type.toLowerCase()));
    }
}

interface IProps {
    type: string;
    title?: string;
    message?: string;
}
