import apiv2 from "@core/apiv2";
import { formatUrl, t } from "@core/application";
import React from "react";
import { withRouter, BrowserRouter, Route, Link } from "react-router-dom";
import { log, logError, debug } from "@core/utility";
import InputTextBlock from "../../Forms/InputTextBlock";
import Checkbox from "../../Forms/Checkbox";
import ButtonSubmit from "../../Forms/ButtonSubmit";
import Paragraph from "../../Forms/Paragraph";
import get from "lodash/get";
import { IRequiredComponentID, getRequiredID } from "@core/Interfaces/componentIDs";

interface IProps {
    location?: any;
    password: string;
    username: string;
}

interface IState extends IRequiredComponentID {
    editable: boolean;
    usernameRef?: InputTextBlock;
    usernameErrors: string[];
    passwordRef?: InputTextBlock;
    passwordErrors: string[];
    redirectTo?: string | null;
    globalError?: string | null;
    submitEnabled: boolean;
    rememberMe: boolean;
}

class PasswordForm extends React.Component<IProps, IState> {
    private username: InputTextBlock;
    private password: InputTextBlock;

    constructor(props) {
        super(props);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.handleTextChange = this.handleTextChange.bind(this);
        this.handleCheckBoxChange = this.handleCheckBoxChange.bind(this);
        this.handleErrors = this.handleErrors.bind(this);

        this.state = {
            id: getRequiredID(props, "passwordForm"),
            editable: true,
            redirectTo: null,
            submitEnabled: false,
            rememberMe: true,
            passwordErrors: [],
            usernameErrors: [],
        };
    }

    public handleTextChange = event => {
        const type: string = get(event, "target.type", "");

        if (type === "password") {
            this.setState({
                globalError: null,
                passwordErrors: [],
            });
        }
        if (type === "text") {
            this.setState({
                globalError: null,
                usernameErrors: [],
            });
        }
    };

    public handleCheckBoxChange = event => {
        const value: boolean = get(event, "target.checked", false);
        this.setState({
            rememberMe: value,
        });
    };

    public handleErrors = e => {
        const errors = get(e, "response.data.errors", []);
        const generalError = get(e, "response.data.message", false);
        const catchAllErrorMessage = t("An error has occurred, please try again.");
        const hasFieldSpecificErrors = errors.length > 0;

        const newState: any = {
            editable: true,
            globalError: null,
            passwordErrors: [],
            usernameErrors: [],
        };

        if (generalError || hasFieldSpecificErrors) {
            if (hasFieldSpecificErrors) {
                // Field Errors
                logError("PasswordForm Errors", errors);
                errors.map((error, index) => {
                    error.timestamp = new Date().getTime(); // Timestamp to make sure state changes, even if the message is the same
                    const targetError = error.field + "Errors";

                    if (newState[targetError]) {
                        newState[targetError] = [...newState[targetError], error];
                    } else {
                        newState[targetError] = [];
                        newState[targetError].push(error);
                    }
                });
            } else {
                // Global message
                newState.globalError = generalError;
            }
        } else {
            // Something went really wrong. Add default message to tell the user there's a problem.
            newState.globalError = catchAllErrorMessage;
        }
        this.setState(newState, () => {
            const hasGlobalError = !!this.state.globalError;
            const hasPasswordError = this.state.passwordErrors.length > 0;
            const hasUsernameError = this.state.usernameErrors.length > 0;

            if (hasGlobalError && !hasPasswordError && !hasUsernameError) {
                this.username.select();
            } else if (hasUsernameError) {
                this.username.select();
            } else if (hasPasswordError) {
                this.password.select();
            }
        });
    };

    public handleSubmit = event => {
        event.preventDefault();

        this.setState({
            editable: false,
        });

        apiv2
            .post("/authenticate/password", {
                username: this.username.value,
                password: this.password.value,
                persist: this.state.rememberMe,
            })
            .then(r => {
                const search = get(this, "props.location.search", "/");
                const params = new URLSearchParams(search);
                window.location.href = formatUrl(params.get("target") || "/");
            })
            .catch(e => {
                this.handleErrors(e);
            });
    };

    public get formDescriptionID() {
        return this.state.id + "-description";
    }

    public render() {
        if (this.state.redirectTo) {
            return (
                <BrowserRouter>
                    <Route path={this.state.redirectTo} component={PasswordForm} />
                </BrowserRouter>
            );
        } else {
            let formDescribedBy;
            if (this.state.globalError) {
                formDescribedBy = this.formDescriptionID;
            }

            return (
                <form
                    id={this.state.id}
                    aria-describedby={formDescribedBy}
                    className="passwordForm"
                    method="post"
                    onSubmit={this.handleSubmit}
                    noValidate
                >
                    <Paragraph
                        id={this.formDescriptionID}
                        className="authenticateUser-paragraph"
                        content={this.state.globalError}
                        isError={true}
                    />
                    <InputTextBlock
                        label={t("Email/Username")}
                        required={true}
                        disabled={!this.state.editable}
                        errors={this.state.usernameErrors}
                        defaultValue={this.props.username}
                        onChange={this.handleTextChange}
                        ref={username => (this.username = username as InputTextBlock)}
                    />
                    <InputTextBlock
                        label={t("Password")}
                        required={true}
                        disabled={!this.state.editable}
                        errors={this.state.passwordErrors}
                        defaultValue={this.props.password}
                        onChange={this.handleTextChange}
                        type="password"
                        ref={password => (this.password = password as InputTextBlock)}
                    />
                    <div className="inputBlock inputBlock-tighter">
                        <div className="rememberMeAndForgot">
                            <span className="rememberMeAndForgot-rememberMe">
                                <Checkbox
                                    label={t("Keep me signed in")}
                                    onChange={this.handleCheckBoxChange}
                                    checked={this.state.rememberMe}
                                />
                            </span>
                            <span className="rememberMeAndForgot-forgot">
                                <Link to="/authenticate/recoverpassword">{t("Forgot your password?")}</Link>
                            </span>
                        </div>
                    </div>
                    <ButtonSubmit disabled={!this.state.editable} content={t("Sign In")} />
                    {/*<p className="authenticateUser-paragraph isCentered">{t('Not registered?')} <Link to="/entry/signup">{t('Create an Account')}</Link></p>*/}
                </form>
            );
        }
    }
}

export default withRouter(PasswordForm);
