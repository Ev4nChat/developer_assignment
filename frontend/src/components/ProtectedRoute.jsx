import React, { useContext } from "react";
import { Navigate } from "react-router-dom";
import AuthContext from "../context/AuthContext";

const ProtectedRoute = ({ children, requiredRole }) => {
    const { token, role } = useContext(AuthContext);

    if (!token || !role) {
        // Not logged in
        return <Navigate to="/login" replace />;
    }

    if (requiredRole && role !== requiredRole) {
        // Role does not match
        return <Navigate to="/login" replace />;
    }

    // Authorized
    return children;
};

export default ProtectedRoute;
