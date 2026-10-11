"""Update Center User sidebar icons to meaningful Tabler Icons."""
import json
from pathlib import Path

MENU_PATH = Path("resources/menu/centerUserMenu.json")

# Professional Tabler icons mapped to menu meaning (beauty/salon center).
ICON_MAP = {
    # Dashboard
    "statistics": "chart-pie",
    # Locations
    "branches": "building-community",
    # Services group
    "services_management": "cut",
    "weeksdays": "calendar-event",
    "shifts": "clock",
    "services": "wand",
    "workers": "id-badge-2",
    "categories": "tags",
    # Payments
    "payment_methods": "credit-card",
    # Catalog
    "global_categories": "category-2",
    "products": "shopping-bag",
    "mainstocks": "building-warehouse",
    "stockorders": "truck-delivery",
    "inventorymovements": "arrows-exchange",
    # Finance
    "sales": "report-money",
    "withdrawal_requests": "cash-banknote",
    "expenses": "receipt-2",
    "expenses_providers": "users-group",
    "suppliers": "truck",
    # Customers
    "users": "heart-handshake",
    # Staff / roles
    "roles_center_users": "lock-access",
    "centeruserroles": "badge",
    "centerusers": "user-plus",
    # Loyalty
    "discount_customers": "gift",
    "memberships": "award",
    "discounts": "percentage",
    "coupons": "wallet",
    "packages": "packages",
    # Comms
    "notifications": "bell-ringing",
    "received_notifications": "mail-opened",
    # Reports
    "reports": "chart-infographic",
    "daily_report": "calendar-stats",
    "sales_report": "chart-area-line",
    "staff_report": "user-star",
    "commission_report": "coin",
    "tips_report": "mood-dollar",
    "expense_report": "file-analytics",
    # Settings
    "settings": "settings-2",
}


def convert(item: dict) -> None:
    name = item.get("name")
    if name in ICON_MAP:
        item["icon"] = ICON_MAP[name]
        item.pop("external_icon", None)
        item.pop("external_icon_dark", None)
    for sub in item.get("submenu", []) or []:
        convert(sub)


def main() -> None:
    data = json.loads(MENU_PATH.read_text(encoding="utf-8"))
    for item in data["menu"]:
        convert(item)
    MENU_PATH.write_text(
        json.dumps(data, indent=2, ensure_ascii=False) + "\n",
        encoding="utf-8",
    )
    for item in data["menu"]:
        print(f"{item.get('name'):28} -> {item.get('icon')}")
        for sub in item.get("submenu", []) or []:
            print(f"  - {sub.get('name'):24} -> {sub.get('icon')}")


if __name__ == "__main__":
    main()
