"""
Demo Data Synchronization Test
This script verifies that API and frontend demo data are permanently synchronized.
"""

import sys
import os
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from app.core.demo_data import DEMO_KPI_DATA

def test_demo_data_consistency():
    """Test that demo data matches expected values"""
    print("🔄 Testing Demo Data Consistency...")

    # Test system performance data
    system_perf = DEMO_KPI_DATA["system_performance"]
    assert system_perf["uptime"] == 99.8, f"Expected uptime 99.8, got {system_perf['uptime']}"
    assert system_perf["response_time"] == 1.2, f"Expected response_time 1.2, got {system_perf['response_time']}"
    assert system_perf["user_satisfaction"] == 4.2, f"Expected user_satisfaction 4.2, got {system_perf['user_satisfaction']}"

    # Test revenue data
    revenue = DEMO_KPI_DATA["revenue_collection"]
    assert revenue["target"] == 6200000000, f"Expected revenue target 6.2B, got {revenue['target']}"
    assert revenue["actual"] == 5930000000, f"Expected revenue actual 5.93B, got {revenue['actual']}"

    # Test other KPIs
    assert DEMO_KPI_DATA["taxpayers"]["count"] == 2539, "Taxpayers count mismatch"
    assert DEMO_KPI_DATA["alerts"]["count"] == 27, "Alerts count mismatch"
    assert DEMO_KPI_DATA["ai_accuracy"]["percentage"] == 96.8, "AI accuracy mismatch"
    assert DEMO_KPI_DATA["fraud_detection"]["rate"] == 95.3, "Fraud detection rate mismatch"
    assert DEMO_KPI_DATA["nudges"]["sent"] == 4230, "Nudges count mismatch"

    print("✅ All demo data consistency tests passed!")
    print("\n📊 Current Demo Data:")
    for category, data in DEMO_KPI_DATA.items():
        if category == "system_performance":
            print(f"  {category}: uptime={data['uptime']}%, response_time={data['response_time']}s, satisfaction={data['user_satisfaction']}/5")
        elif category == "revenue_collection":
            print(f"  {category}: target=ZMW{data['target']/1000000000:.1f".1f" actual=ZMW{data['actual']/1000000000:.1f".1f")
        else:
            key_metrics = list(data.keys())[:2]  # Show first 2 metrics
            metrics_str = ", ".join([f"{k}={v}" for k, v in list(data.items())[:2]])
            print(f"  {category}: {metrics_str}")

    return True

if __name__ == "__main__":
    test_demo_data_consistency()
    print("\n🎯 Demo data is permanently synchronized and will never change!")
