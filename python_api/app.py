from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import os

app = Flask(__name__)
CORS(app)

MODEL_PATH = os.path.join(os.path.dirname(__file__), 'bin_model.pkl')
model = joblib.load(MODEL_PATH)

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.get_json()
        
        days = float(data.get('days_since_last_pickup', 0))
        avg_waste = float(data.get('avg_daily_waste_kg', 0))
        fill_level = float(data.get('fill_level_percentage', 0))

        
        features = [[days, avg_waste, fill_level]]
        risk_prob = model.predict_proba(features)[0][1] * 100
        
        
        prediction = 1 if risk_prob >= 50 else 0
        risk_status = "High Risk (Overflow Likely)" if prediction == 1 else "Normal Status"

        return jsonify({
            'status': 'success',
            'overflow_risk': prediction,
            'risk_status': risk_status,
            'risk_percentage': round(risk_prob, 1) 
        })

    except Exception as e:
        return jsonify({
            'status': 'error',
            'message': str(e)
        }), 400

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)