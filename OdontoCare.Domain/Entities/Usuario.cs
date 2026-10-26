namespace OdontoCare.Domain.Entities;

public sealed class Usuario
{
    public int IdUsuario { get; set; }
    public string Nome { get; set; } = "";
    public string? Email { get; set; }
    public string Login { get; set; } = "";
    public string Senha { get; set; } = ""; // Apenas hash PBKDF2, NUNCA serializar entidade.
    public string TipoUsuario { get; set; } = "";
}
