from decimal import Decimal

from scripts.import_catalogue_csv import category_for_fabric, clean_int, clean_price, slugify, split_values


def test_clean_price_indian_format():
    assert clean_price("₹1,899/-") == Decimal("1899.00")
    assert clean_price("1,299") == Decimal("1299.00")
    assert clean_price("") is None


def test_clean_int():
    assert clean_int("1,200") == 1200
    assert clean_int("0") == 0
    assert clean_int("-1") is None


def test_category_mapping():
    assert category_for_fabric("Pure Dola Silk") == "dola-silk"
    assert category_for_fabric("Soft Georgette") == "georgette"
    assert category_for_fabric("Cotton Linen") == "cotton-linen"
    assert category_for_fabric("Banarasi Silk") == "sarees"


def test_slug_and_split():
    assert slugify("TS-001 Royal Dola Saree") == "ts-001-royal-dola-saree"
    assert split_values("Pink, Blue | Green") == ["Pink", "Blue", "Green"]
