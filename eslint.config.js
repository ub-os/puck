import js from "@eslint/js";
import babelParser from "@babel/eslint-parser";
import importPlugin from 'eslint-plugin-import';

export default [
    js.configs.recommended,
    importPlugin.flatConfigs.recommended,
    {
        languageOptions: {
            parser: babelParser,
            ecmaVersion: "latest",
            sourceType: "module",
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
        },

        settings: {
            "import/resolver": {
                "babel-module": {},
            },
        },

        rules: {
            "no-unused-vars": "off",
            "no-undef": "off",
        },
    },
];