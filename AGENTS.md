# AGENTS.md

## 项目结构要点

- Laravel 博客扩展包（`chuoke/laravel-blog`）。后台为 Inertia + Vue 3 页面，前台为 Blade 主题（default / minimal / magazine）。
- 后台 Vue 源码通过 `php artisan vendor:publish --tag=blog-assets` 拷贝发布到宿主应用的 `resources/js/Pages/Blog`，**包内没有 Vue 构建链**，由宿主应用编译。
- 前台主题 CSS 用 `npm run build:theme`（Tailwind 4 CLI）构建到 `resources/css/blog/`。
- 样式约定：统一使用 DaisyUI 语义 token（`primary`、`base-*`、`base-content` 等），不要使用 Tailwind 默认调色板（`primary-600`、`slate-*` 等在 DaisyUI 下是失效类）。

## 决策记录

- **界面多语言（i18n）**：2026-09 决定推迟到包基本稳定、有实际应用后再做。已定方向：词典放 PHP 端 `lang/`（Blade 主题只能用 PHP 翻译），Inertia 后台经 `Inertia::share` 桥接 + `t()` composable，不引入 vue-i18n。实施时注意：`MdEditor` 的 `language` 目前写死 `en-US`，需跟随 locale。
