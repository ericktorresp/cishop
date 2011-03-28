# coding=utf-8
from django.utils import simplejson
from dajaxice.core import dajaxice_functions
from home.models import Country, Province, City

def get_province(request, country):
    """
    QuerySet can't serializable，so, here must convert it to list
    """
    provinces = Province.objects.filter(country__exact=country).values('id', 'name')
    response_dict = {}
    response_dict.update({'provinces':list(provinces)})
    return simplejson.dumps(response_dict)

dajaxice_functions.register(get_province)

def get_city(request, province):
    cities = City.objects.filter(province__exact=province).values('id', 'city')
    return simplejson.dumps({'cities':list(cities)})

dajaxice_functions.register(get_city)