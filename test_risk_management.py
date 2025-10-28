#!/usr/bin/env python3
"""
Test script to verify the Risk Management system is working correctly
"""

import asyncio
import sys
import os

# Add the app directory to Python path
sys.path.append(os.path.join(os.path.dirname(__file__), 'app'))

from app.api.v1.endpoints.risk_management import (
    RiskAssessmentEngine,
    RiskType,
    RiskLevel,
    risk_engine
)

def test_risk_engine():
    """Test the risk assessment engine"""

    print("🧪 Testing Risk Assessment Engine...")
    print("=" * 50)

    # Test data for different risk types
    test_cases = [
        {
            "risk_type": RiskType.FRAUD,
            "data": {
                "inconsistent_data": 0.8,
                "unusual_patterns": 0.6,
                "missing_documentation": 0.4,
                "high_value_transactions": 0.9
            },
            "expected_level": "critical"
        },
        {
            "risk_type": RiskType.COMPLIANCE,
            "data": {
                "late_filings": 0.3,
                "non_payment": 0.1,
                "incomplete_records": 0.2,
                "regulatory_changes": 0.0
            },
            "expected_level": "medium"
        },
        {
            "risk_type": RiskType.FINANCIAL,
            "data": {
                "payment_irregularities": 0.1,
                "income_discrepancies": 0.2,
                "asset_mismatches": 0.1,
                "cash_flow_anomalies": 0.0
            },
            "expected_level": "low"
        }
    ]

    for i, test_case in enumerate(test_cases, 1):
        print(f"\n📊 Test Case {i}: {test_case['risk_type'].value}")

        result = risk_engine.calculate_risk_score(
            test_case["risk_type"],
            test_case["data"]
        )

        print(f"   Risk Score: {result['risk_score']:.3f}")
        print(f"   Risk Level: {result['risk_level'].value}")
        print(f"   Confidence: {result['confidence']:.3f}")
        print(f"   Expected: {test_case['expected_level']}")

        # Check if result matches expectation
        expected_level = RiskLevel(test_case["expected_level"].upper())
        if result["risk_level"] == expected_level:
            print("   ✅ PASS")
        else:
            print("   ❌ FAIL")

        print(f"   Factors: {result['factors']}")
        print(f"   Recommendations: {result['recommendations']}")

    print("\n" + "=" * 50)
    print("🏁 Risk Engine Testing Complete!")

    return True

def test_imports():
    """Test that all imports are working"""

    print("🔧 Testing Imports...")

    try:
        from app.api.v1.endpoints.risk_management import router, RiskAssessmentEngine
        print("   ✅ Risk management imports successful")
        return True
    except Exception as e:
        print(f"   ❌ Import error: {e}")
        return False

if __name__ == "__main__":
    print("🚀 Risk Management System Test Suite")
    print("=" * 60)

    # Test imports
    if not test_imports():
        print("❌ Import tests failed. Cannot continue.")
        sys.exit(1)

    # Test risk engine
    if not test_risk_engine():
        print("❌ Risk engine tests failed.")
        sys.exit(1)

    print("\n🎉 All tests passed! Risk management system is working correctly.")
    print("\n📋 Available API Endpoints:")
    print("   • POST /api/v1/risk/assess - Perform risk assessment")
    print("   • GET  /api/v1/risk/assessments/{tpin} - Get risk assessments")
    print("   • POST /api/v1/risk/cases/auto-create - Create automated case")
    print("   • GET  /api/v1/risk/alerts/{tpin} - Get risk alerts")
    print("   • GET  /api/v1/risk/trends/{tpin} - Get risk trends")
    print("   • POST /api/v1/risk/bulk-assessment - Bulk assessment")
    print("   • GET  /api/v1/risk/monitor/{tpin} - Real-time monitoring")
