using OdontoCare.Application.Features.Auth.DTOs;
using OdontoCare.Application.Features.Consultas.DTOs;

namespace OdontoCare.Application.Features.Consultas.Interfaces;

public interface IConsultaLookupService
{
    Task<List<ConsultaListaDto>> ListarAsync(int? usuarioId);
    Task<List<UsuarioDto>> ListarUsuariosAsync();
    Task<List<ProcedimentoDto>> ListarProcedimentosAsync();
    Task<UsuarioDto?> BuscarUsuarioAsync(int id);
}
