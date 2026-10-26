namespace OdontoCare.Application.Security;

public static class Roles
{
    // Os nomes dos perfis respeitam o banco legado (Secretária possui acento).
    public const string Administrador = "Administrador";
    public const string Dentista = "Dentista";
    public const string Secretaria = "Secretária";
    public const string Gestao = Administrador + "," + Secretaria;
    public const string Todos = Administrador + "," + Secretaria + "," + Dentista;
    public static bool Valido(string? perfil) => perfil is Administrador or Dentista or Secretaria;
    public static string Normalizar(string? perfil) => perfil?.Trim() switch
    {
        "Secretaria" or "Recepcionista" => Secretaria,
        "Profissional" => Dentista,
        var valor => valor ?? ""
    };
}
