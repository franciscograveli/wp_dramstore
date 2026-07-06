# UAE AI Tools — Setup Guide

Connect AI assistants like Claude, Cursor, and VS Code to your WordPress site and let them manage headers, footers, templates, pages, and widgets through Ultimate Addons for Elementor.

---

## Requirements

| Requirement | Details |
|---|---|
| **WordPress** | 7.0+ (includes Abilities API and MCP Adapter in core) |
| **UAE Plugin** | 2.9.0+ (Header Footer Elementor) |
| **Elementor** | 3.0+ |
| **AI Client** | Claude Desktop, Claude Code, Cursor, VS Code, Windsurf, Codex, or any MCP-compatible client |

> **WordPress 6.9 users:** Install the [MCP Adapter plugin](https://github.com/WordPress/mcp-adapter) separately. On WordPress 7.0+, it is included in core.

---

## How It Works

UAE registers **56+ AI abilities** (tools) with the WordPress Abilities API. The MCP Adapter — built into WordPress 7.0 — translates these abilities into the Model Context Protocol (MCP), which AI clients understand natively.

When you connect an AI client, it discovers all available UAE tools and can:

- **Read** plugin info, templates, pages, widgets, extensions, display rules, theme settings, and design tokens
- **Create** headers, footers, before-footer templates, and pages with Elementor widgets
- **Edit** template content, display rules, widget settings, and page metadata
- **Manage** widget activation, extension toggles, cache clearing, and more

All operations respect WordPress capabilities — AI clients act as the authenticated user.

---

## Quick Start (5 Minutes)

### Step 1: Create an Application Password

1. Go to **Users > Profile** in WordPress admin
2. Scroll to **Application Passwords**
3. Enter a name (e.g., "Claude Desktop") and click **Add New Application Password**
4. Copy the generated password — you will not see it again

### Step 2: Get Your Connection Config

1. Go to **UAE > Settings > AI Tools** in WordPress admin
2. Enter your username and Application Password
3. Click **Generate Configs**
4. Copy the config for your AI client

### Step 3: Add to Your AI Client

Follow the client-specific instructions below.

---

## Client Setup

### Claude Desktop

Claude Desktop uses a stdio bridge to communicate with remote WordPress sites. This requires **Node.js 18+** installed on your computer.

**Config file location:**
- macOS: `~/Library/Application Support/Claude/claude_desktop_config.json`
- Windows: `%APPDATA%\Claude\claude_desktop_config.json`

**Add this to the file:**

```json
{
  "mcpServers": {
    "uae": {
      "command": "npx",
      "args": ["-y", "@automattic/mcp-wordpress-remote@latest"],
      "env": {
        "WP_API_URL": "https://yoursite.com/wp-json/uae/mcp",
        "WP_API_USERNAME": "your-username",
        "WP_API_PASSWORD": "xxxx xxxx xxxx xxxx xxxx xxxx"
      }
    }
  }
}
```

**Restart Claude Desktop** after saving.

> **Alternative:** In Claude Desktop, go to **Settings > Connectors > Add custom connector** and enter your server URL directly. This method does not require Node.js.

> **Troubleshooting:** If you see `ReadableStream is not defined`, your Node.js version is too old. Run `node -v` and upgrade to 18+ if needed.

---

### Claude Code

Claude Code supports direct HTTP connections.

**Create `.mcp.json` in your project root (or `~/.claude/.mcp.json` for global access):**

```json
{
  "mcpServers": {
    "uae": {
      "type": "http",
      "url": "https://yoursite.com/wp-json/uae/mcp",
      "headers": {
        "Authorization": "Basic BASE64_ENCODED_CREDENTIALS"
      }
    }
  }
}
```

**Generate Base64 credentials:**

```bash
echo -n "username:application-password" | base64
```

Or use the credentials generator on the UAE AI Tools settings page.

---

### Cursor

**Create `.cursor/mcp.json` in your project root:**

```json
{
  "mcpServers": {
    "uae": {
      "url": "https://yoursite.com/wp-json/uae/mcp",
      "headers": {
        "Authorization": "Basic BASE64_ENCODED_CREDENTIALS"
      }
    }
  }
}
```

Or go to **Cursor Settings > Tools and MCP > Add Custom MCP** and paste the config.

---

### VS Code (GitHub Copilot)

**Create `.vscode/mcp.json` in your project root:**

```json
{
  "servers": {
    "uae": {
      "type": "http",
      "url": "https://yoursite.com/wp-json/uae/mcp",
      "headers": {
        "Authorization": "Basic BASE64_ENCODED_CREDENTIALS"
      }
    }
  }
}
```

> Note: VS Code uses `servers` instead of `mcpServers`.

---

### Windsurf

**Add to `~/.codeium/windsurf/mcp_config.json`:**

```json
{
  "mcpServers": {
    "uae": {
      "serverUrl": "https://yoursite.com/wp-json/uae/mcp",
      "headers": {
        "Authorization": "Basic BASE64_ENCODED_CREDENTIALS"
      }
    }
  }
}
```

---

### Antigravity (Gemini)

**Add to `~/.gemini/antigravity/mcp_config.json`:**

```json
{
  "mcpServers": {
    "uae": {
      "serverUrl": "https://yoursite.com/wp-json/uae/mcp",
      "headers": {
        "Authorization": "Basic BASE64_ENCODED_CREDENTIALS"
      }
    }
  }
}
```

---

### Codex (OpenAI)

**Add to `~/.codex/config.toml`:**

```toml
[mcp_servers.uae]
url = "https://yoursite.com/wp-json/uae/mcp"

[mcp_servers.uae.http_headers]
"Authorization" = "Basic BASE64_ENCODED_CREDENTIALS"
```

---

## Available AI Tools

### Read-Only Tools (Always Available)

| Category | Tool | Description |
|---|---|---|
| **Plugin Info** | Get Info | Plugin version, health status, active hooks |
| **Active Templates** | Get Active | Which header/footer renders on a given URL |
| **Templates** | List Templates | Browse all headers, footers, and blocks |
| **Templates** | Get Template | Read template details and Elementor data |
| **Pages** | List Pages | Browse WordPress pages |
| **Widgets** | List Widgets | All available Elementor widgets and their status |
| **Widgets** | Widget Usage | Which widgets are used across templates |
| **Extensions** | List Extensions | Scroll to Top, Reading Progress Bar status |
| **Display Rules** | Get Locations | Available display rule locations |
| **Theme** | Theme Info | Active theme detection and compatibility |
| **Theme** | Theme Method | Current theme rendering method |
| **Settings** | Get Settings | Plugin configuration values |
| **Design System** | Design Tokens | Site colors, fonts, and spacing from Elementor |
| **Builder** | Widget Types | Available widget types and their schemas |
| **Builder** | Structure | Read Elementor page/template structure |
| **Builder** | CSS | Read custom CSS for a post |
| **Builder** | Schema | Widget control schemas for building |
| **Pro** | Pro Features | UAE Pro upgrade info and pricing |

### Write Tools (Requires "Allow Modifications")

| Category | Tool | Description |
|---|---|---|
| **Templates** | Create Template | Create new header, footer, or block |
| **Templates** | Update Template | Edit template title, type, or display rules |
| **Templates** | Delete Template | Move template to trash |
| **Templates** | Restore Template | Restore trashed template |
| **Templates** | Duplicate Template | Clone an existing template |
| **Pages** | Create Page | Create a new WordPress page |
| **Pages** | Delete Page | Move page to trash |
| **Pages** | Restore Page | Restore trashed page |
| **Pages** | Update Status | Change page publish status |
| **Pages** | Update Meta | Edit page title, slug, featured image |
| **Widgets** | Activate Widget | Enable a widget for use |
| **Widgets** | Deactivate Widget | Disable a widget |
| **Widgets** | Bulk Toggle | Enable/disable multiple widgets |
| **Widgets** | Deactivate Unused | Disable all unused widgets |
| **Extensions** | Toggle Extension | Enable/disable Scroll to Top or Progress Bar |
| **Display Rules** | Update Rules | Change where templates appear |
| **Settings** | Update Settings | Change plugin configuration |
| **Builder** | Build | Set full Elementor content for a post |
| **Builder** | Insert Widget | Add a widget to a container |
| **Builder** | Update Widget | Change widget settings |
| **Builder** | Remove Element | Delete a section, column, or widget |
| **Builder** | Move Element | Reorder elements in the layout |
| **Builder** | Add Section | Add a new section to the layout |
| **Builder** | Add Column | Add a column to a section |
| **Maintenance** | Clear Cache | Flush Elementor CSS and plugin caches |

---

## Settings

### Standalone UAE Server

When enabled, UAE creates a dedicated MCP server at `/wp-json/uae/mcp` exposing only UAE abilities. This keeps your AI client focused on UAE tools without seeing other plugins' abilities.

When disabled, UAE abilities are still available on the default WordPress MCP server at `/wp-json/mcp/mcp-adapter-default-server` alongside other plugins' abilities.

### Allow Modifications

Controls whether write operations are available to AI clients:

- **Off** (default): AI can only read data — list templates, view settings, inspect widgets
- **On**: AI can create, edit, and delete templates, pages, and settings

### Angie Integration

When the Angie plugin is installed and active, UAE abilities are available to Elementor's in-browser AI assistant. This works independently of the MCP server.

### Ability Toggles

Fine-grained control over which individual abilities are exposed to AI clients. Disable specific tools you do not want AI to access.

---

## Security Best Practices

1. **Create a dedicated WordPress user** for AI access with only the roles and capabilities needed
2. **Use Application Passwords** — they can be revoked individually without changing your main password
3. **Start with read-only access** — only enable "Allow Modifications" when you need AI to make changes
4. **Disable unused abilities** — turn off tools you do not need
5. **Use HTTPS** — always use an SSL certificate on production sites
6. **Revoke passwords** when no longer needed — go to Users > Profile > Application Passwords

---

## Architecture

```
AI Client (Claude, Cursor, VS Code)
    |
    | MCP Protocol (HTTP or stdio bridge)
    |
WordPress MCP Adapter (built into WP 7.0)
    |
    | Abilities API
    |
UAE Plugin (registers 56+ abilities)
    |
    | WordPress APIs
    |
Your Site Content (templates, pages, widgets, settings)
```

**UAE registers abilities once.** The MCP Adapter handles protocol translation, transport, and session management automatically. AI clients discover available tools through the standard MCP protocol.

---

## Troubleshooting

### "Requires WordPress 6.8+" message on settings page

The Abilities API is not available. Upgrade to WordPress 7.0+ or install the [MCP Adapter plugin](https://github.com/WordPress/mcp-adapter) on WordPress 6.9.

### Claude Desktop shows no tools

1. Verify Node.js 18+ is installed: `node -v`
2. Check the config file path is correct for your OS
3. Ensure the WordPress site is accessible from your network
4. Verify the Application Password is correct (no extra spaces)
5. Restart Claude Desktop after editing the config

### "ReadableStream is not defined" error

Your Node.js version is too old. The `@automattic/mcp-wordpress-remote` package requires Node.js 18+.

```bash
# Check version
node -v

# Upgrade with nvm
nvm install 20
nvm use 20
```

### Tools appear but return permission errors

The AI client authenticates as a WordPress user. Ensure that user has the required capabilities:
- **Read operations**: `edit_posts` capability
- **Template management**: `edit_posts` + `publish_posts`
- **Settings changes**: `manage_options`
- **Page management**: `edit_pages` + `publish_pages`

### Abilities list is empty on settings page

1. Check that the MCP Adapter is active (built into WP 7.0, or install the plugin on WP 6.9)
2. Clear any object cache or page cache
3. Reload the settings page

### Write tools are not available

Enable **Allow Modifications** in UAE > Settings > AI Tools. Write tools are disabled by default for safety.

---

## FAQ

**Q: Does this work with WordPress.com?**
A: This guide is for self-hosted WordPress (WordPress.org). WordPress.com has its own MCP integration through the Claude Connector.

**Q: Can multiple AI clients connect at the same time?**
A: Yes. Each client authenticates independently with its own Application Password. You can create separate passwords for each client.

**Q: Do I need the MCP Adapter plugin?**
A: On WordPress 7.0+, no — it is included in core. On WordPress 6.9, yes — install it from the [WordPress GitHub repository](https://github.com/WordPress/mcp-adapter).

**Q: What happens if I deactivate UAE?**
A: All UAE abilities are unregistered. AI clients will no longer see UAE tools. Your templates and content are not affected.

**Q: Is my content sent to AI providers?**
A: Only when the AI client requests it through a tool call. UAE does not send data to any external service on its own. The AI client (e.g., Claude) processes the data according to its own privacy policy.

**Q: Can I use this on a local development site?**
A: Yes. For local sites (`.local`, `.test`, `.dev` domains), use the Claude Desktop config with `@automattic/mcp-wordpress-remote` which handles local SSL certificates. Claude Code and other HTTP clients work directly with local URLs.
