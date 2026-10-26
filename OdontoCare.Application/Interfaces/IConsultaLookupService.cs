using OdontoCare.Application.DTOs;

namespace OdontoCare.Application.Interfaces;
public interface IConsultaLookupService
{
    Task<List<ConsultaListaDto>> ListarAsync(int? usuarioId);
    Task<List<UsuarioDto>> ListarUsuariosAsync();
    Task<List<ProcedimentoDto>> ListarProcedimentosAsync();
    Task<UsuarioDto?> BuscarUsuarioAsync(int id);
}
