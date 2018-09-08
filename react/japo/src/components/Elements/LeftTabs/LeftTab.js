import React from 'react';

class LeftTab extends React.Component {

    static defaultProps = {
        isTab: true,
    }

    render() {
        return (
            <div className="left-tab">{this.props.children}</div>
        );
    };
}

export default LeftTab;