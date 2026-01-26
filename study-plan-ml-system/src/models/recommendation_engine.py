import numpy as np
import pandas as pd
from sklearn.cluster import KMeans
from sklearn.decomposition import NMF
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.preprocessing import StandardScaler
from typing import Dict, List, Tuple, Any, Optional
import random
from datetime import datetime, timedelta
from src.models.base_model import BaseModel
from src.models.user_profiling import UserProfilingModel
from src.models.knowledge_mastery import KnowledgeMasteryModel
from src.utils.logger import setup_logger
import logging

# Setup logger with default values
try:
    logger = setup_logger('recommendation_engine', 'logs/recommendation_engine.log')
except:
    # Fallback to basic logging if setup_logger fails
    logging.basicConfig(level=logging.INFO)
    logger = logging.getLogger('recommendation_engine')

class StudyPlanRecommendationEngine(BaseModel):
    """
    Study Plan Recommendation Layer using collaborative filtering, clustering,
    and rule-based approaches to generate personalized study plans.
    """
    
    def __init__(self, approach: str = 'hybrid'):
        super().__init__(model_name="recommendation_engine")
        self.approach = approach  # 'collaborative', 'clustering', 'rule_based', 'hybrid'
        self.user_profiler = None
        self.knowledge_assessor = None
        self.scaler = StandardScaler()
        self.cluster_model = None
        self.similarity_matrix = None
        self.user_item_matrix = None
        
    def build_model(self, **kwargs) -> Any:
        """Build the recommendation model based on selected approach."""
        
        if self.approach == 'collaborative':
            # Matrix factorization for collaborative filtering
            self.model = NMF(
                n_components=kwargs.get('n_components', 10),
                init='random',
                random_state=42
            )
            
        elif self.approach == 'clustering':
            # K-Means clustering for grouping similar learners
            self.model = KMeans(
                n_clusters=kwargs.get('n_clusters', 5),
                random_state=42
            )
            
        elif self.approach == 'hybrid':
            # Combination of multiple approaches
            self.model = {
                'clustering': KMeans(n_clusters=kwargs.get('n_clusters', 5), random_state=42),
                'collaborative': NMF(n_components=kwargs.get('n_components', 10), random_state=42)
            }
            
        else:  # rule_based
            self.model = None  # Rule-based doesn't need ML model
            
        self.model_params = kwargs
        logger.info(f"Built {self.approach} recommendation engine")
        return self.model
    
    def set_component_models(self, user_profiler: UserProfilingModel, knowledge_assessor: KnowledgeMasteryModel):
        """Set the user profiling and knowledge mastery models."""
        self.user_profiler = user_profiler
        self.knowledge_assessor = knowledge_assessor
    
    def create_user_item_matrix(self, interaction_data: pd.DataFrame) -> pd.DataFrame:
        """Create user-item interaction matrix for collaborative filtering."""
        
        if 'user_id' not in interaction_data.columns or 'topic' not in interaction_data.columns:
            raise ValueError("interaction_data must contain 'user_id' and 'topic' columns")
        
        # Use accuracy as the interaction strength
        if 'accuracy' in interaction_data.columns:
            matrix = interaction_data.pivot_table(
                index='user_id',
                columns='topic',
                values='accuracy',
                fill_value=0,
                aggfunc='mean'
            )
        else:
            # Binary interaction matrix
            matrix = interaction_data.groupby(['user_id', 'topic']).size().unstack(fill_value=0)
            matrix = (matrix > 0).astype(int)
        
        self.user_item_matrix = matrix
        return matrix
    
    def train(self, X: pd.DataFrame, y: pd.Series = None, **kwargs) -> Dict[str, float]:
        """Train the recommendation model."""
        
        # Create user-item matrix for collaborative filtering
        if self.approach in ['collaborative', 'hybrid']:
            user_item_matrix = self.create_user_item_matrix(X)
            
            if self.approach == 'collaborative':
                # Train matrix factorization
                self.model.fit(user_item_matrix.values)
            else:  # hybrid
                self.model['collaborative'].fit(user_item_matrix.values)
        
        # For clustering approaches, use user features
        if self.approach in ['clustering', 'hybrid']:
            # Engineer user features from interaction data
            user_features = self._engineer_user_features(X)
            user_features_scaled = self.scaler.fit_transform(user_features)
            
            if self.approach == 'clustering':
                self.model.fit(user_features_scaled)
            else:  # hybrid
                self.model['clustering'].fit(user_features_scaled)
        
        self.is_trained = True
        
        # Calculate basic metrics
        metrics = {'training_completed': 1.0}
        
        if self.approach in ['clustering', 'hybrid']:
            cluster_model = self.model if self.approach == 'clustering' else self.model['clustering']
            metrics['inertia'] = cluster_model.inertia_
            
        logger.info(f"Recommendation engine trained using {self.approach} approach")
        return metrics
    
    def predict(self, X: pd.DataFrame) -> np.ndarray:
        """Generate study plan recommendations."""
        
        if not self.is_trained:
            raise ValueError("Model must be trained before making predictions")
        
        recommendations = []
        
        for user_id in X['user_id'].unique():
            user_data = X[X['user_id'] == user_id]
            user_rec = self._generate_user_recommendations(user_data, user_id)
            recommendations.append(user_rec)
        
        return np.array(recommendations)
    
    def _engineer_user_features(self, data: pd.DataFrame) -> pd.DataFrame:
        """Engineer features for user clustering."""
        
        features = pd.DataFrame()
        
        # Performance features
        if 'accuracy' in data.columns:
            features['avg_accuracy'] = data.groupby('user_id')['accuracy'].mean()
            features['accuracy_std'] = data.groupby('user_id')['accuracy'].std().fillna(0)
        
        # Activity patterns
        if 'response_timestamp' in data.columns:
            data['response_timestamp'] = pd.to_datetime(data['response_timestamp'])
            data['hour'] = data['response_timestamp'].dt.hour
            features['avg_hour'] = data.groupby('user_id')['hour'].mean()
            features['hour_std'] = data.groupby('user_id')['hour'].std().fillna(0)
        
        # Topic preferences
        if 'topic' in data.columns:
            topic_counts = data.groupby(['user_id', 'topic']).size().unstack(fill_value=0)
            topic_prefs = topic_counts.div(topic_counts.sum(axis=1), axis=0)
            features = pd.concat([features, topic_prefs], axis=1)
        
        # Session patterns
        if 'session_duration' in data.columns:
            features['avg_session_duration'] = data.groupby('user_id')['session_duration'].mean()
            features['session_duration_std'] = data.groupby('user_id')['session_duration'].std().fillna(0)
        
        features = features.fillna(0)
        return features
    
    def _generate_user_recommendations(self, user_data: pd.DataFrame, user_id: int) -> Dict[str, Any]:
        """Generate personalized study plan for a user."""
        
        recommendations = {
            'user_id': user_id,
            'study_plan': {},
            'next_topics': [],
            'time_slots': [],
            'difficulty_progression': {},
            'break_suggestions': []
        }
        
        # Get user profile insights
        if self.user_profiler and self.user_profiler.is_trained:
            user_insights = self.user_profiler.get_user_insights(user_data)
            recommendations['user_profile'] = user_insights
        
        # Get knowledge mastery insights
        if self.knowledge_assessor and self.knowledge_assessor.is_trained:
            learning_insights = self.knowledge_assessor.get_learning_insights(user_data)
            recommendations['knowledge_assessment'] = learning_insights
        
        # Generate topic recommendations based on approach
        if self.approach == 'collaborative':
            recommendations['next_topics'] = self._collaborative_topic_recommendations(user_id)
        elif self.approach == 'clustering':
            recommendations['next_topics'] = self._clustering_topic_recommendations(user_data)
        elif self.approach == 'rule_based':
            recommendations['next_topics'] = self._rule_based_topic_recommendations(user_data)
        else:  # hybrid
            collaborative_topics = self._collaborative_topic_recommendations(user_id)
            clustering_topics = self._clustering_topic_recommendations(user_data)
            rule_based_topics = self._rule_based_topic_recommendations(user_data)
            
            # Combine recommendations with weights
            all_topics = {}
            for topic, score in collaborative_topics:
                all_topics[topic] = all_topics.get(topic, 0) + score * 0.4
            for topic, score in clustering_topics:
                all_topics[topic] = all_topics.get(topic, 0) + score * 0.3
            for topic, score in rule_based_topics:
                all_topics[topic] = all_topics.get(topic, 0) + score * 0.3
            
            recommendations['next_topics'] = sorted(all_topics.items(), key=lambda x: x[1], reverse=True)[:5]
        
        # Generate time slot recommendations
        recommendations['time_slots'] = self._recommend_time_slots(user_data)
        
        # Generate difficulty progression
        recommendations['difficulty_progression'] = self._recommend_difficulty_progression(user_data)
        
        # Generate break suggestions
        recommendations['break_suggestions'] = self._recommend_breaks(user_data)
        
        # Create detailed study plan
        recommendations['study_plan'] = self._create_detailed_study_plan(recommendations)
        
        return recommendations
    
    def _collaborative_topic_recommendations(self, user_id: int) -> List[Tuple[str, float]]:
        """Generate topic recommendations using collaborative filtering."""
        
        if self.user_item_matrix is None:
            return []
        
        if user_id not in self.user_item_matrix.index:
            # New user - recommend popular topics
            topic_popularity = self.user_item_matrix.mean(axis=0)
            return [(topic, score) for topic, score in topic_popularity.nlargest(5).items()]
        
        # Get user's interaction vector
        user_vector = self.user_item_matrix.loc[user_id].values.reshape(1, -1)
        
        # Find similar users
        similarities = cosine_similarity(user_vector, self.user_item_matrix.values)[0]
        similar_users = self.user_item_matrix.index[np.argsort(similarities)[-6:-1]]  # Top 5 similar users
        
        # Get topics they engaged with highly
        similar_user_prefs = self.user_item_matrix.loc[similar_users].mean(axis=0)
        
        # Remove topics user has already engaged with
        user_topics = self.user_item_matrix.loc[user_id]
        unengaged_topics = user_topics[user_topics == 0].index
        
        recommendations = [(topic, similar_user_prefs[topic]) for topic in unengaged_topics]
        return sorted(recommendations, key=lambda x: x[1], reverse=True)[:5]
    
    def _clustering_topic_recommendations(self, user_data: pd.DataFrame) -> List[Tuple[str, float]]:
        """Generate topic recommendations based on user cluster."""
        
        if self.approach not in ['clustering', 'hybrid']:
            return []
        
        # Get user features
        user_features = self._engineer_user_features(user_data)
        if len(user_features) == 0:
            return []
        
        user_features_scaled = self.scaler.transform(user_features)
        
        # Get user's cluster
        cluster_model = self.model if self.approach == 'clustering' else self.model['clustering']
        user_cluster = cluster_model.predict(user_features_scaled)[0]
        
        # Find topics popular in this cluster
        # This would require training data to be stored, simplified here
        cluster_topics = [
            ('mathematics', 0.8),
            ('science', 0.7),
            ('programming', 0.6),
            ('literature', 0.5),
            ('history', 0.4)
        ]
        
        return cluster_topics[:3]
    
    def _rule_based_topic_recommendations(self, user_data: pd.DataFrame) -> List[Tuple[str, float]]:
        """Generate topic recommendations using rule-based approach."""
        
        recommendations = []
        
        # Rule 1: Recommend topics with low performance for improvement
        if 'topic' in user_data.columns and 'accuracy' in user_data.columns:
            topic_performance = user_data.groupby('topic')['accuracy'].mean()
            weak_topics = topic_performance[topic_performance < 0.6]
            
            for topic, accuracy in weak_topics.items():
                recommendations.append((topic, 1.0 - accuracy))  # Higher score for weaker topics
        
        # Rule 2: Recommend prerequisite topics
        prerequisite_map = {
            'advanced_math': ['basic_math'],
            'calculus': ['algebra', 'trigonometry'],
            'advanced_programming': ['basic_programming'],
            'data_science': ['statistics', 'programming']
        }
        
        current_topics = user_data['topic'].unique() if 'topic' in user_data.columns else []
        for advanced_topic, prerequisites in prerequisite_map.items():
            if advanced_topic not in current_topics:
                prereq_completed = all(prereq in current_topics for prereq in prerequisites)
                if prereq_completed:
                    recommendations.append((advanced_topic, 0.8))
        
        # Rule 3: Recommend topics based on time patterns
        if 'response_timestamp' in user_data.columns:
            user_data['hour'] = pd.to_datetime(user_data['response_timestamp']).dt.hour
            if user_data['hour'].mean() < 12:  # Morning person
                recommendations.append(('challenging_topics', 0.9))
            else:  # Evening person
                recommendations.append(('review_topics', 0.7))
        
        return sorted(recommendations, key=lambda x: x[1], reverse=True)[:5]
    
    def _recommend_time_slots(self, user_data: pd.DataFrame) -> List[Dict[str, Any]]:
        """Recommend optimal study time slots."""
        
        time_slots = []
        
        # Analyze user's activity patterns
        if 'response_timestamp' in user_data.columns:
            user_data['hour'] = pd.to_datetime(user_data['response_timestamp']).dt.hour
            active_hours = user_data['hour'].value_counts().head(3)
            
            for hour, count in active_hours.items():
                time_slots.append({
                    'start_time': f"{hour:02d}:00",
                    'end_time': f"{(hour+1)%24:02d}:00",
                    'confidence': min(count / len(user_data), 1.0),
                    'type': 'high_activity'
                })
        else:
            # Default recommendations
            time_slots = [
                {'start_time': '09:00', 'end_time': '10:00', 'confidence': 0.7, 'type': 'morning'},
                {'start_time': '14:00', 'end_time': '15:00', 'confidence': 0.6, 'type': 'afternoon'},
                {'start_time': '19:00', 'end_time': '20:00', 'confidence': 0.8, 'type': 'evening'}
            ]
        
        return time_slots
    
    def _recommend_difficulty_progression(self, user_data: pd.DataFrame) -> Dict[str, str]:
        """Recommend difficulty progression for different topics."""
        
        progression = {}
        
        if 'topic' in user_data.columns and 'accuracy' in user_data.columns:
            topic_performance = user_data.groupby('topic')['accuracy'].mean()
            
            for topic, accuracy in topic_performance.items():
                if accuracy >= 0.8:
                    progression[topic] = 'hard'
                elif accuracy >= 0.6:
                    progression[topic] = 'medium'
                else:
                    progression[topic] = 'easy'
        
        return progression
    
    def _recommend_breaks(self, user_data: pd.DataFrame) -> List[Dict[str, Any]]:
        """Recommend break patterns based on user behavior."""
        
        breaks = []
        
        # Analyze session patterns
        if 'session_duration' in user_data.columns:
            avg_session = user_data['session_duration'].mean()
            
            if avg_session > 60:  # Long sessions
                breaks.append({
                    'type': 'micro_break',
                    'duration': 5,
                    'frequency': 'every_25_minutes',
                    'description': 'Short 5-minute breaks during long study sessions'
                })
            
            breaks.append({
                'type': 'medium_break',
                'duration': 15,
                'frequency': 'after_each_session',
                'description': 'Medium break after completing each study session'
            })
        
        # Default break recommendations
        if not breaks:
            breaks = [
                {
                    'type': 'pomodoro',
                    'duration': 5,
                    'frequency': 'every_25_minutes',
                    'description': 'Pomodoro technique: 5-minute breaks every 25 minutes'
                },
                {
                    'type': 'long_break',
                    'duration': 30,
                    'frequency': 'every_2_hours',
                    'description': 'Longer break every 2 hours of studying'
                }
            ]
        
        return breaks
    
    def _create_detailed_study_plan(self, recommendations: Dict[str, Any]) -> Dict[str, Any]:
        """Create a detailed study plan from recommendations."""
        
        study_plan = {
            'daily_schedule': [],
            'weekly_goals': {},
            'monthly_objectives': [],
            'adaptive_elements': []
        }
        
        # Create daily schedule
        time_slots = recommendations.get('time_slots', [])
        next_topics = recommendations.get('next_topics', [])
        
        for i, slot in enumerate(time_slots[:3]):  # Limit to 3 sessions per day
            if i < len(next_topics):
                topic, priority = next_topics[i]
                difficulty = recommendations.get('difficulty_progression', {}).get(topic, 'medium')
                
                study_plan['daily_schedule'].append({
                    'time': f"{slot['start_time']} - {slot['end_time']}",
                    'topic': topic,
                    'difficulty': difficulty,
                    'priority': priority,
                    'estimated_duration': 60,  # minutes
                    'break_after': 15  # minutes
                })
        
        # Weekly goals
        for topic, priority in next_topics[:5]:
            study_plan['weekly_goals'][topic] = {
                'target_accuracy': 0.8,
                'sessions_per_week': 3,
                'priority': priority
            }
        
        # Monthly objectives
        study_plan['monthly_objectives'] = [
            'Complete all high-priority topics',
            'Achieve 80% accuracy in target areas',
            'Establish consistent study routine'
        ]
        
        # Adaptive elements
        study_plan['adaptive_elements'] = [
            'Adjust difficulty based on performance',
            'Modify schedule based on consistency',
            'Update topic priorities based on progress'
        ]
        
        return study_plan
    
    def generate_study_schedule(self, user_id: int, user_data: pd.DataFrame, days: int = 7) -> Dict[str, Any]:
        """Generate a detailed study schedule for the specified number of days."""
        
        recommendations = self._generate_user_recommendations(user_data, user_id)
        
        schedule = {
            'user_id': user_id,
            'schedule_duration': f"{days} days",
            'daily_plans': {},
            'summary': {
                'total_study_hours': 0,
                'topics_covered': len(recommendations['next_topics']),
                'difficulty_distribution': {}
            }
        }
        
        # Generate daily plans
        for day in range(days):
            date = (datetime.now() + timedelta(days=day)).strftime('%Y-%m-%d')
            
            daily_plan = {
                'date': date,
                'sessions': [],
                'total_duration': 0,
                'breaks': []
            }
            
            # Assign topics to this day
            day_topics = recommendations['next_topics'][day % len(recommendations['next_topics']):day % len(recommendations['next_topics']) + 2]
            
            for i, (topic, priority) in enumerate(day_topics):
                time_slot = recommendations['time_slots'][i % len(recommendations['time_slots'])]
                difficulty = recommendations['difficulty_progression'].get(topic, 'medium')
                
                session = {
                    'session_id': f"session_{day}_{i}",
                    'topic': topic,
                    'start_time': time_slot['start_time'],
                    'end_time': time_slot['end_time'],
                    'difficulty': difficulty,
                    'priority': priority,
                    'duration_minutes': 60,
                    'objectives': [
                        f"Study {topic} concepts",
                        f"Complete practice problems",
                        f"Review previous mistakes"
                    ]
                }
                
                daily_plan['sessions'].append(session)
                daily_plan['total_duration'] += session['duration_minutes']
            
            # Add breaks
            daily_plan['breaks'] = recommendations['break_suggestions']
            
            schedule['daily_plans'][date] = daily_plan
            schedule['summary']['total_study_hours'] += daily_plan['total_duration'] / 60
        
        return schedule
