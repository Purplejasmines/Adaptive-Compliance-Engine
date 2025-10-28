#!/usr/bin/env python3
"""
Simple API test script to verify the integration
"""

import requests
import json

def test_api_integration():
    """Test the API endpoints"""

    base_url = "http://localhost:8000/api/v1"

    print("🧪 Testing API Integration...")
    print("=" * 50)

    # Test 1: Check if server is responding
    try:
        response = requests.get(f"{base_url}/tax/status/demo-tpin", timeout=5)
        print("✅ Server is responding")
        print(f"   Status Code: {response.status_code}")

        if response.status_code == 200:
            data = response.json()
            print("✅ Taxpayer status endpoint working")
            print(f"   Sample Data: {json.dumps(data, indent=2)}")
        else:
            print(f"⚠️  API returned: {response.text}")

    except requests.exceptions.RequestException as e:
        print(f"❌ Connection failed: {e}")
        return False

    # Test 2: Check taxpayer endpoints
    try:
        response = requests.get(f"{base_url}/taxpayers/list", timeout=5)
        print(f"\n✅ Taxpayer list endpoint: {response.status_code}")
    except Exception as e:
        print(f"\n⚠️  Taxpayer list endpoint error: {e}")

    # Test 3: Check business endpoints
    try:
        response = requests.get(f"{base_url}/businesses/list", timeout=5)
        print(f"✅ Business list endpoint: {response.status_code}")
    except Exception as e:
        print(f"⚠️  Business list endpoint error: {e}")

    # Test 4: Check tax calculation
    try:
        calc_data = {
            "tpin": "demo-tpin",
            "tax_type": "paye",
            "tax_period": "2024",
            "gross_income": 50000,
            "taxable_income": None,
            "deductions": 0
        }
        response = requests.post(f"{base_url}/tax/calculate", json=calc_data, timeout=5)
        print(f"\n✅ Tax calculation endpoint: {response.status_code}")
        if response.status_code == 200:
            data = response.json()
            print(f"   Calculated Tax: ZMW {data.get('calculated_tax', 0):,.2f}")
    except Exception as e:
        print(f"⚠️  Tax calculation endpoint error: {e}")

    print("\n" + "=" * 50)
    print("🎉 API Integration Test Complete!")
    print("\n📋 Available API Endpoints:")
    print("   • GET  /api/v1/tax/status/{tpin} - Taxpayer status")
    print("   • GET  /api/v1/taxpayers/list - List taxpayers")
    print("   • GET  /api/v1/businesses/list - List businesses")
    print("   • POST /api/v1/tax/calculate - Calculate tax")
    print("   • POST /api/v1/tax/returns - File tax return")
    print("   • POST /api/v1/tax/payments - Record payment")
    print("   • POST /api/v1/auth/login - Taxpayer login")
    print("   • POST /api/v1/auth/register - Taxpayer registration")

    return True

if __name__ == "__main__":
    test_api_integration()
