use App\Models\User;
use Illuminate\Support\Facades\Hash;

if (User::where('email', 'admin@example.com')->doesntExist()) {
    $u = new User();
    $u->name = 'Admin User';
    $u->email = 'admin@example.com';
    $u->password = Hash::make('password123');
    $u->save();
    echo "Created\n";
} else {
    $u = User::where('email', 'admin@example.com')->first();
    $u->password = Hash::make('password123');
    $u->save();
    echo "Exists, updated password\n";
}
