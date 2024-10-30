import {__} from "@wordpress/i18n";

const CspPluginButton = ({setModalOpen}) => {
    const onClick = () => {
        setModalOpen(true)
    }

    return (
        <div>
            <button
                className="button button-primary csp-plugin-meta-box-action-button"
                onClick={onClick}
            >
                {__('Find links', 'csp-plugin')}
            </button>
        </div>
    )
}

export default CspPluginButton