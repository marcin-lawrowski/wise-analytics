import React from "react";

class TooltipIcon extends React.Component {

    render() {
        // TODO: install boostrap js
        return <i className={`ml-1 fas fa2 ${this.props.icon}`} style={ { fontSize: this.props.iconSize + 'em' } } data-bs-toggle="tooltip" data-bs-placement="top" title="Tooltip on top" />;
    }

}

TooltipIcon.defaultProps = {
    iconSize: 'inherit',
    icon: 'fa-question-circle',
    placement: 'top'
};

export default TooltipIcon;