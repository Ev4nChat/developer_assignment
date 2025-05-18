import React from "react";
import { Routes, Route } from "react-router-dom";
import Login from "../pages/Login";
import ManagerDashboard from "../pages/ManagerDashboard";
import EmployeeDashboard from "../pages/EmployeeDashboard";
import ProtectedRoute from "../components/ProtectedRoute";

const AppRoutes = () => {
    return (
        <Routes>
            {/* Public */}
            <Route path="/login" element={<Login />} />

            {/* Protected - Manager only */}
            <Route
                path="/manager/dashboard"
                element={
                    <ProtectedRoute requiredRole="ROLE_MANAGER">
                        <ManagerDashboard />
                    </ProtectedRoute>
                }
            />

            {/* Protected - Employee only */}
            <Route
                path="/employee/dashboard"
                element={
                    <ProtectedRoute requiredRole="ROLE_EMPLOYEE">
                        <EmployeeDashboard />
                    </ProtectedRoute>
                }
            />

            {/* Default route */}
            <Route path="*" element={<Login />} />
        </Routes>
    );
};

export default AppRoutes;
