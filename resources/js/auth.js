export const setAuth = (user, token) => {
    localStorage.setItem("user", JSON.stringify(user));
    localStorage.setItem("token", token);
};

export const getUser = () => {
    return JSON.parse(localStorage.getItem("user"));
};

export const logout = () => {
    localStorage.removeItem("user");
    localStorage.removeItem("token");
    window.location.href = "/login";
};
