<?php

namespace App;

use App\User\Group;
use App\User\Role;
use App\User\CostCenter;
use App\User\Warehouses;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $xml_colibri_sap
 * @property bool $porcionamento
 * @property bool $admin
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property bool|null $ativo
 * @property string|null $permissoes
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAtivo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePermissoes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePorcionamento($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereXmlColibriSap($value)
 * @mixin \Eloquent
 * @property int|null $group_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereGroupId($value)
 * @property-read \App\User\Group|null $group
 * @property-read Role[] $roles
 * @property string|null $tipo
 * @property string|null $whsDefault
 * @property int|null $userClerk
 * @property int|null $whsGroup
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereTipo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUserClerk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereWhsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereWhsGroup($value)
 */
class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'group_id', 'ativo', 'tipo','tipoTransf','tipoCompra','freeCompra', 'whsDefault', 'userClerk','whsGroup','permissions'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-d-m',
        'updated_at' => 'datetime:Y-d-m',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, "user_group_user_role",
            "user_group_id", "user_role_id", "group_id");
    }

    public function costCenters()
    {
        return $this->hasMany(CostCenter::class, 'user_id', 'id');
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouses::class, 'user_id', 'id');
    }

    public function hasRole($requestedRole)
    {
        foreach ($this->roles as $role) {
            if($role->code === "admin") {
                return true;
            }
            if($requestedRole === $role->code) {
                return true;
            }
        }
        return false;
    }


    public function createPermissions($data){
        
        return json_encode([
            'configuration' => $this->checkPermission($data,'configuration'),
            'config_boot' => $this->checkPermission($data,'config_boot'),
            'config_sale_point' => $this->checkPermission($data,'config_sale_point'),
            'config_users' => $this->checkPermission($data,'config_users'),
            'config_approvers' => $this->checkPermission($data,'config_approvers'),
            'config_users_group' => $this->checkPermission($data,'config_users_group'),
            'config_whs_group' => $this->checkPermission($data,'config_whs_group'),
            'erros' => $this->checkPermission($data,'erros'),
            'nfcs' => $this->checkPermission($data,'nfcs'),
            'portioning' => $this->checkPermission($data,'portioning'),
            'portion_search' => $this->checkPermission($data,'portion_search'),
            'portion_list' => $this->checkPermission($data,'portion_list'),
            'portion_loss' => $this->checkPermission($data,'portion_loss'),
            'portion_justify' => $this->checkPermission($data,'portion_justify'),
            'portion_loss_justify' => $this->checkPermission($data,'portion_loss_justify'),
            'intern_consumption' => $this->checkPermission($data,'intern_consumption'),
            'intern_consumption_perdas' => $this->checkPermission($data,'intern_consumption_perdas'),
            'intern_consumption_eventos' => $this->checkPermission($data,'intern_consumption_eventos'),
            'inventoryx' => $this->checkPermission($data,'inventoryx'),
            'inventory_request' => $this->checkPermission($data,'inventory_request'),
            'inventory_input' => $this->checkPermission($data,'inventory_input'),
            'inventory_output' => $this->checkPermission($data,'inventory_output'),
            'inventory_transfer_taking' => $this->checkPermission($data,'inventory_transfer_taking'),
            'inventory_transfer' => $this->checkPermission($data,'inventory_transfer'),
            'inventory_stock_loan' => $this->checkPermission($data,'inventory_stock_loan'),
            'inventory_items' => $this->checkPermission($data,'inventory_items'),
            'inventory_items_new' => $this->checkPermission($data,'inventory_items_new'),
            'inventory_items_edit' => $this->checkPermission($data,'inventory_items_edit'),
            'accounting' => $this->checkPermission($data,'accounting'),
            'account_lcm' => $this->checkPermission($data,'account_lcm'),
            'b_partners' => $this->checkPermission($data,'b_partners'),
            'b_partner' => $this->checkPermission($data,'b_partner'),
            'purchasex' => $this->checkPermission($data,'purchasex'),
            'purchase_order' => $this->checkPermission($data,'purchase_order'),
            'purchase_suggestion_order' => $this->checkPermission($data,'purchase_suggestion_order'),
            'purchase_request' => $this->checkPermission($data,'purchase_request'),
            'purchase_suggestion_request' => $this->checkPermission($data,'purchase_suggestion_request'),
            'purchase_quotation' => $this->checkPermission($data,'purchase_quotation'),
            'purchase_nfc' => $this->checkPermission($data,'purchase_nfc'),
            'purchase_advance_provider' => $this->checkPermission($data,'purchase_advance_provider'),
            'purchase_order_budget_relatory' => $this->checkPermission($data,'purchase_order_budget_relatory'),
            'tomticket' => $this->checkPermission($data,'tomticket'),
            'dashboard_menu' => $this->checkPermission($data,'dashboard_menu'),
            'dashboard_purchase' => $this->checkPermission($data,'dashboard_purchase'),
            'dashboard_finances' => $this->checkPermission($data,'dashboard_finances'),
        ]);
        
    }
    
    //check para permissões de nivel de acesso
    public function checkPermission($data, $type){

        switch ($type) {
            //Configurações
            case 'configuration':
                if(isset($data['configuration']) && ($data['configuration'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'config_boot':
                if(isset($data['config_boot']) && ($data['config_boot'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'config_sale_point':
                if(isset($data['config_sale_point']) && ($data['config_sale_point'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'config_users':
                if(isset($data['config_users']) && ($data['config_users'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'config_approvers':
                if(isset($data['config_approvers']) && ($data['config_approvers'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'config_users_group':
                if(isset($data['config_users_group']) && ($data['config_users_group'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'config_whs_group':
                if(isset($data['config_whs_group']) && ($data['config_whs_group'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;

            //Erros
            case 'erros':
                if(isset($data['erros']) && ($data['erros'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            //Nfcs
            case 'nfcs':
                if(isset($data['nfcs']) && ($data['nfcs'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            //Porcionamento
            case 'portioning':
                if(isset($data['portioning']) && ($data['portioning'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'portion_search':
                if(isset($data['portion_search']) && ($data['portion_search'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'portion_list':
                if(isset($data['portion_list']) && ($data['portion_list'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'portion_loss':
                if(isset($data['portion_loss']) && ($data['portion_loss'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'portion_justify':
                if(isset($data['portion_justify']) && ($data['portion_justify'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'portion_loss_justify':
                if(isset($data['portion_loss_justify']) && ($data['portion_loss_justify'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
             //Consumo interno
            case 'intern_consumption':
                if(isset($data['intern_consumption']) && ($data['intern_consumption'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'intern_consumption_perdas':
                if(isset($data['intern_consumption_perdas']) && ($data['intern_consumption_perdas'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'intern_consumption_eventos':
                if(isset($data['intern_consumption_eventos']) && ($data['intern_consumption_eventos'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
             //Estoque
             case 'inventoryx':
                if(isset($data['inventoryx']) && ($data['inventoryx'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
             case 'inventory_request':
                if(isset($data['inventory_request']) && ($data['inventory_request'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
             case 'inventory_input':
                if(isset($data['inventory_input']) && ($data['inventory_input'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
             case 'inventory_output':
                if(isset($data['inventory_output']) && ($data['inventory_output'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
             case 'inventory_transfer_taking':
                if(isset($data['inventory_transfer_taking']) && ($data['inventory_transfer_taking'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
             case 'inventory_transfer':
                if(isset($data['inventory_transfer']) && ($data['inventory_transfer'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
             case 'inventory_stock_loan':
                if(isset($data['inventory_stock_loan']) && ($data['inventory_stock_loan'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'inventory_items':
                if(isset($data['inventory_items']) && ($data['inventory_items'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'inventory_items_new':
                if(isset($data['inventory_items_new']) && ($data['inventory_items_new'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'inventory_items_edit':
                if(isset($data['inventory_items_edit']) && ($data['inventory_items_edit'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            //Contabilidade
            case 'accounting':
                if(isset($data['accounting']) && ($data['accounting'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'account_lcm':
                if(isset($data['account_lcm']) && ($data['account_lcm'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            //Parceiro
            case 'b_partners':
                if(isset($data['b_partners']) && ($data['b_partners'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'b_partner':
                if(isset($data['b_partner']) && ($data['b_partner'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            //Compras
            case 'purchasex':
                if(isset($data['purchasex']) && ($data['purchasex'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'purchase_order':
                if(isset($data['purchase_order']) && ($data['purchase_order'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'purchase_suggestion_order':
                if(isset($data['purchase_suggestion_order']) && ($data['purchase_suggestion_order'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'purchase_request':
                if(isset($data['purchase_request']) && ($data['purchase_request'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'purchase_suggestion_request':
                if(isset($data['purchase_suggestion_request']) && ($data['purchase_suggestion_request'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'purchase_quotation':
                if(isset($data['purchase_quotation']) && ($data['purchase_quotation'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'purchase_nfc':
                if(isset($data['purchase_nfc']) && ($data['purchase_nfc'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'purchase_advance_provider':
                if(isset($data['purchase_advance_provider']) && ($data['purchase_advance_provider'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            case 'purchase_order_budget_relatory':
                    if(isset($data['purchase_order_budget_relatory']) && ($data['purchase_order_budget_relatory'] == 'on')){
                    return true;
                    }else{
                    return false;
                    }
                    break;
            case 'tomticket':
                if(isset($data['tomticket']) && ($data['tomticket'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;

            case 'dashboard_menu':
                if(isset($data['dashboard_menu']) && ($data['dashboard_menu'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;

            case 'dashboard_purchase':
                if(isset($data['dashboard_purchase']) && ($data['dashboard_purchase'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;

            case 'dashboard_finances':
                if(isset($data['dashboard_finances']) && ($data['dashboard_finances'] == 'on')){
                return true;
                }else{
                return false;
                }
                break;
            
        }
            
    }
}
