import { createVuetify } from "vuetify";
import "@mdi/font/css/materialdesignicons.css";
import * as directives from "vuetify/directives";

export default createVuetify({
    directives,
    theme: {
        defaultTheme: 'defaultTheme',
        themes: {
            defaultTheme: {
                variables: {
                    "border-color": "#a4abb3",
                },
                colors: {
                    primary: "#00b265",
                    secondary: "#49BEFF",
                    info: "#539BFF",
                    success: "#13DEB9",
                    accent: "#FFAB91",
                    warning: "#FFAE1F",
                    error: "#fc3443",
                    lightprimary: "#ECF2FF",
                    lightsecondary: "#E8F7FF",
                    lightsuccess: "#E6FFFA",
                    lighterror: "#FDEDE8",
                    lightinfo: "#EBF3FE",
                    lightwarning: "#FEF5E5",
                    textPrimary: "#2A3547",
                    textSecondary: "#2A3547",
                    borderColor: "#e5eaef",
                    inputBorder: "#c8cdd3",
                    containerBg: "#ffffff",
                    background: "#ffffff",
                    hoverColor: "#f6f9fc",
                    surface: "#fff",
                    "on-surface-variant": "#fff",
                    grey100: "#F2F6FA",
                    grey200: "#EAEFF4",
                },
            },
        },
    },
    defaults: {
        VCard: {
            rounded: "md",
        },
        VTextField: {
            variant: "outlined",
            density: "comfortable",
            color: "primary",
        },
        VTextarea: {
            variant: "outlined",
            density: "comfortable",
            color: "primary",
        },
        VSelect: {
            variant: "outlined",
            density: "comfortable",
            color: "primary",
        },
        VAutocomplete: {
            variant: "outlined",
            density: "comfortable",
            color: "primary",
        },
        VListItem: {
            minHeight: "45px",
        },
        VTooltip: {
            location: "top",
        },
    },
});
