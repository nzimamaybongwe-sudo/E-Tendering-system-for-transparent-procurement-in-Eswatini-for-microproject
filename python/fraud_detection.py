import numpy as np
import pandas as pd
from sklearn.linear_model import LogisticRegression
from sklearn.preprocessing import StandardScaler

# Example training data for suspicious bidding patterns
# Each row is a bid event with engineered features.
# In a real system, generate this from historical tender records.
train_data = pd.DataFrame([
    {'price_similarity': 0.01, 'submission_gap': 5, 'same_location': 1, 'bid_age_days': 30, 'label': 1},
    {'price_similarity': 0.50, 'submission_gap': 20, 'same_location': 0, 'bid_age_days': 180, 'label': 0},
    {'price_similarity': 0.03, 'submission_gap': 2, 'same_location': 1, 'bid_age_days': 15, 'label': 1},
    {'price_similarity': 0.60, 'submission_gap': 90, 'same_location': 0, 'bid_age_days': 40, 'label': 0},
])

features = ['price_similarity', 'submission_gap', 'same_location', 'bid_age_days']
X = train_data[features]
y = train_data['label']

scaler = StandardScaler()
X_scaled = scaler.fit_transform(X)

model = LogisticRegression(random_state=42)
model.fit(X_scaled, y)

# Inspect model coefficients (commented out for clean output)
# print('Fraud detection model trained:')
# print('Coefficients:', model.coef_[0])
# print('Intercept:', model.intercept_[0])

import sys

def score_bid(price_similarity, submission_gap, same_location, bid_age_days):
    row = np.array([[price_similarity, submission_gap, same_location, bid_age_days]])
    row_scaled = scaler.transform(row)
    probability = model.predict_proba(row_scaled)[0][1]
    return probability

if __name__ == '__main__':
    if len(sys.argv) == 5:
        price_similarity = float(sys.argv[1])
        submission_gap = int(sys.argv[2])
        same_location = int(sys.argv[3])
        bid_age_days = int(sys.argv[4])
        
        suspicious_probability = score_bid(price_similarity, submission_gap, same_location, bid_age_days)
        # Print only the float value
        print(f'{suspicious_probability:.4f}')
    else:
        print("0.0000")
