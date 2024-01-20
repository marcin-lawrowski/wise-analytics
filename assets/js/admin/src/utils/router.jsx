import React from "react";
import { useLocation } from "react-router-dom";

export function withLocation(Component) {
	return function(props) {
		return <Component location={ useLocation() } {...props} />;
	};
}