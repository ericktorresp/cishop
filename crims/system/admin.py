from system.models import *
from django.contrib import admin

class DrugAdmin(admin.ModelAdmin):
    list_display = ('title', 'price', 'stamina', 'spirit')
    
class BuildingAdmin(admin.ModelAdmin):
    list_display = ('title', 'price', 'expend', 'output', 'drug')
    
class BusinessAdmin(admin.ModelAdmin):
    list_display = ('title', 'price', 'expend', 'type', 'max_vistors', 'limit')
    
class CharacterAdmin(admin.ModelAdmin):
    list_display = ('title', 'intelligence', 'strength', 'charisma', 'tolerance', 'avatar')

class BenefitAdmin(admin.ModelAdmin):
    list_display = ('title', 'type', 'credits')
    
class GuardAdmin(admin.ModelAdmin):
    list_display = ('title', 'strength', 'price')

class HookerAdmin(admin.ModelAdmin):
    list_display = ('title', 'price', 'expend', 'visitprice', 'is_random', 'stamina', 'spirit')

class WeaponAdmin(admin.ModelAdmin):
    list_display = ('title', 'damages', 'price', 'skill', 'proficiency', 'type')

class EventAdmin(admin.ModelAdmin):
    list_display = ('title', 'section', 'change', 'drug')
    
class ProvinceAdmin(admin.ModelAdmin):
    list_display = ('title', 'icon')

class ArmorAdmin(admin.ModelAdmin):
    list_display = ('title', 'tolerance', 'price')

class HospitalAdmin(admin.ModelAdmin):
    list_display = ('title', 'type', 'price')

class RandomEventChoiceInline(admin.StackedInline):
    model = RandomEventChoice
    classes = ('collapse-open',)
    allow_add = False
class RandomEventQuestionAdmin(admin.ModelAdmin):
    inlines = [RandomEventChoiceInline]

class RobberyAdmin(admin.ModelAdmin):
    list_display = ('title', 'difficulty', 'type', 'attribute_range', 'cash_range', 'created',)

class SabotageAdmin(admin.ModelAdmin):
    list_display = ('title', 'stamina', 'difficulty', 'expend',)
admin.site.register(Avatar)
admin.site.register(Armor, ArmorAdmin)
admin.site.register(Character, CharacterAdmin)
admin.site.register(Drug, DrugAdmin)
admin.site.register(Building, BuildingAdmin)
admin.site.register(Hooker, HookerAdmin)
admin.site.register(Business, BusinessAdmin)
admin.site.register(Guard, GuardAdmin)
admin.site.register(Weapon, WeaponAdmin)
admin.site.register(Province, ProvinceAdmin)
admin.site.register(Event, EventAdmin)
admin.site.register(Benefit, BenefitAdmin)
admin.site.register(Hospital, HospitalAdmin)
admin.site.register(RandomEvent)
admin.site.register(RandomEventQuestion, RandomEventQuestionAdmin)
admin.site.register(Robbery, RobberyAdmin)
admin.site.register(Sabotage, SabotageAdmin)
